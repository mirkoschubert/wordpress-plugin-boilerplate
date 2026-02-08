import { useState, useEffect, useCallback } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'
import { __ } from '@wordpress/i18n'

import type {
  UseModulesReturn,
  ModulesApiResponse,
  ApiResponse,
  ModuleInfo,
} from '@/types'

const isDebug = !!window.wpPluginBoilerplateConfig?.debug

export const useModules = (): UseModulesReturn => {
  const [modules, setModules] = useState<Record<string, ModuleInfo>>({})
  const [isLoading, setIsLoading] = useState(true)
  const [error, setError] = useState<string | null>(null)

  const fetchModules = useCallback(async () => {
    try {
      setIsLoading(true)
      setError(null)

      if (isDebug) console.log('WPPluginBoilerplate: Config:', window.wpPluginBoilerplateConfig)
      if (!window.wpPluginBoilerplateConfig) {
        throw new Error('wpPluginBoilerplateConfig is not defined')
      }

      const apiUrl = `${window.location.origin}/wp-json/wp-plugin-boilerplate/v1/modules`

      const restNonce = window.wpPluginBoilerplateConfig?.nonce || ''

      if (!restNonce) {
        throw new Error('No REST API nonce available')
      }

      if (isDebug) console.log('WPPluginBoilerplate: Using nonce:', restNonce.substring(0, 10) + '...')

      const response = await fetch(apiUrl, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
          'Content-Type': 'application/json',
          'X-WP-Nonce': restNonce
        }
      })

      if (isDebug) console.log('WPPluginBoilerplate: Response status:', response.status)

      if (!response.ok) {
        const errorText = await response.text()
        if (isDebug) console.error('WPPluginBoilerplate: API error response:', errorText)
        throw new Error(`HTTP ${response.status}: ${response.statusText}`)
      }

      const data = await response.json() as ModulesApiResponse

      if (data.success && data.data) {
        const processedModules: Record<string, ModuleInfo> = {}

        Object.entries(data.data).forEach(([slug, moduleData]) => {
          const module = moduleData as ModuleInfo
          processedModules[slug] = module
        })

        setModules(processedModules)

        if (isDebug) {
          console.log('WPPluginBoilerplate: Loaded modules:', Object.keys(processedModules))
        }
      } else {
        setError('Failed to load modules - invalid response')
      }
    } catch (err) {
      if (isDebug) console.error('WPPluginBoilerplate: API error:', err)
      setError(err instanceof Error ? err.message : 'Error loading modules')
    } finally {
      setIsLoading(false)
    }
  }, [])

  useEffect(() => {
    fetchModules()
  }, [fetchModules])

  const toggleModule = useCallback(
    async (moduleSlug: string, enabled: boolean): Promise<ApiResponse> => {
      try {
        if (isDebug) console.log(`WPPluginBoilerplate: Toggling ${moduleSlug}:`, { enabled })

        const response = await apiFetch<ApiResponse>({
          path: `/wp-plugin-boilerplate/v1/modules/${moduleSlug}`,
          method: 'POST',
          data: { enabled },
        })

        if (response.success) {
          setModules((prev) => ({
            ...prev,
            [moduleSlug]: {
              ...prev[moduleSlug],
              enabled,
            },
          }))
          if (isDebug) console.log(`WPPluginBoilerplate: ${moduleSlug} toggled successfully`)
        }

        return response
      } catch (err) {
        if (isDebug) console.error(`WPPluginBoilerplate: Error toggling ${moduleSlug}:`, err)
        throw err
      }
    },
    []
  )

  const updateModuleSettings = useCallback(
    async (
      moduleSlug: string,
      settings: Record<string, unknown>
    ): Promise<ApiResponse> => {
      try {
        if (isDebug) console.log(`WPPluginBoilerplate: Updating settings for ${moduleSlug}:`, settings)

        const response = await apiFetch<ApiResponse>({
          path: `/wp-plugin-boilerplate/v1/modules/${moduleSlug}/settings`,
          method: 'POST',
          data: settings,
        })

        if (response.success) {
          setModules((prev) => ({
            ...prev,
            [moduleSlug]: {
              ...prev[moduleSlug],
              options: {
                ...prev[moduleSlug].options,
                ...settings,
              },
            },
          }))
          if (isDebug) console.log(`WPPluginBoilerplate: Settings for ${moduleSlug} updated`)
        }

        return response
      } catch (err) {
        if (isDebug) console.error(`WPPluginBoilerplate: Error updating ${moduleSlug}:`, err)
        throw err
      }
    },
    []
  )

  return {
    modules,
    isLoading,
    error,
    toggleModule,
    updateModuleSettings,
    reload: fetchModules,
  }
}
