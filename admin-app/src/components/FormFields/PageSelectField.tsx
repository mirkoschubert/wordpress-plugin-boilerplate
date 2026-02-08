import { useState, useEffect, forwardRef } from '@wordpress/element'
import { CustomSelectControl, Spinner } from '@wordpress/components'
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import type { FieldConfig } from '@/types'

import './FormFields.styl'

interface Page {
  id: number
  title: { rendered: string }
  parent: number
  slug: string
}

interface PageOption {
  key: string
  name: string
}

interface PageSelectFieldProps {
  id: string
  config: FieldConfig
  value: number
  onChange: (value: number) => void
  className?: string
  style?: React.CSSProperties
}

const PageSelectField = forwardRef<HTMLDivElement, PageSelectFieldProps>((
  {
    id,
    config,
    value,
    onChange,
    className = '',
    style,
  },
  ref
) => {
  const [pages, setPages] = useState<Page[]>([])
  const [loading, setLoading] = useState(true)

  useEffect(() => {
    // Fetch all pages from WordPress REST API
    apiFetch<Page[]>({
      path: '/wp/v2/pages?per_page=100&orderby=title&order=asc&status=publish'
    })
      .then((data) => {
        setPages(data)
        setLoading(false)
      })
      .catch((error) => {
        console.error('Error fetching pages:', error)
        setLoading(false)
      })
  }, [])

  /**
   * Build hierarchical options with indentation
   */
  const buildHierarchicalOptions = (): PageOption[] => {
    const options: PageOption[] = [
      { key: '0', name: __('— None (use default /jobs/)', 'wp-plugin-boilerplate') }
    ]

    const addChildren = (parentId: number, level: string) => {
      pages
        .filter(page => page.parent === parentId)
        .forEach(page => {
          const indent = level
          const name = indent
            ? `${indent} ${page.title.rendered}`
            : page.title.rendered

          options.push({
            key: String(page.id),
            name: name
          })

          // Recursively add children
          addChildren(page.id, indent + '—')
        })
    }

    // Start with top-level pages (parent === 0)
    addChildren(0, '')

    return options
  }

  const options = buildHierarchicalOptions()
  const selectedOption = options.find(opt => opt.key === String(value)) || options[0]

  return (
    <div ref={ref} className={`dvc-field page-select-field ${className}`} style={style}>
      <div className="dvc-field-header">
        <h4 className="dvc-field-label">{config.label}</h4>
        {config.description && (
          <p className="dvc-field-description">{config.description}</p>
        )}
      </div>

      {loading ? (
        <div style={{ display: 'flex', alignItems: 'center', gap: '8px', padding: '8px 0' }}>
          <Spinner />
          <span>{__('Loading pages...', 'wp-plugin-boilerplate')}</span>
        </div>
      ) : (
        <CustomSelectControl
          __next40pxDefaultSize
          hideLabelFromVision
          label={config.label || ''}
          value={selectedOption}
          options={options}
          onChange={({ selectedItem }: { selectedItem: PageOption }) => {
            onChange(parseInt(selectedItem.key, 10))
          }}
        />
      )}
    </div>
  )
})

PageSelectField.displayName = 'PageSelectField'

export default PageSelectField
