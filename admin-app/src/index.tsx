import { render } from '@wordpress/element'
import App from './App'


// Warten bis DOM bereit ist
document.addEventListener('DOMContentLoaded', () => {
  const container = document.getElementById('wp-plugin-boilerplate-react-admin')
  if (container) {
    render(<App />, container)
  }
})
