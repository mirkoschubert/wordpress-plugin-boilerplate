# Custom Post Types Module

This module provides custom post type registration with ACF field groups in the WP Plugin Boilerplate.

## Jobs Custom Post Type

The Jobs custom post type includes:

- ✅ Proper CPT registration with translatable labels
- ✅ Custom taxonomies (Categories and Locations)
- ✅ Programmatic ACF field group registration (ensures translatability)
- ✅ Conditional ACF loading (only if ACF plugin is available)
- ✅ Admin settings integration
- ✅ Gutenberg editor support

## Adding Additional Custom Post Types

To add more custom post types to this module:

1. **Copy the Service methods** for `register_jobs_cpt()`, `register_jobs_taxonomies()`, and `register_jobs_acf_fields()`
2. **Rename** all instances of `job` to your post type slug
3. **Update labels** to match your content type
4. **Modify ACF fields** to match your data requirements
5. **Add module settings** in `CustomPostTypes.php` for your new CPT
6. **Update Service.php** `init_service()` to conditionally register your CPT

## ACF Field Types Used in Example

- `date_picker` - Start date, application deadline
- `select` - Job type, experience level, remote work options
- `number` - Salary range
- `email` - Contact email
- `url` - External application URL

## Best Practices Demonstrated

### 1. Translatable Strings
All labels use `__()`, `_x()`, or `_e()` with the 'wp-plugin-boilerplate' text domain.

### 2. Programmatic ACF Registration
Fields are registered via `acf_add_local_field_group()` instead of JSON export, making them translatable.

### 3. Dependency Checking
ACF fields only load if ACF is active:
```php
if (function_exists('acf_add_local_field_group')) {
    add_action('acf/init', [$this, 'register_jobs_acf_fields']);
}
```

### 4. Configurable via Admin UI
CPT behavior can be toggled and configured without code changes.

### 5. Gutenberg Support
`'show_in_rest' => true` enables the block editor.

## Fields Included

| Field | Type | Purpose |
|-------|------|---------|
| Start Date | Date Picker | When position starts |
| Application Deadline | Date Picker | Last day to apply |
| Job Type | Select | Full-time, Part-time, Contract, etc. |
| Experience Level | Select | Entry, Junior, Mid, Senior, etc. |
| Salary Range | Number | Min/Max salary (optional) |
| Remote Work | Select | On-site, Hybrid, Remote |
| Contact Email | Email | Application contact |
| Application URL | URL | External application link |

## Taxonomies Included

- **Job Category** (Hierarchical) - Department, team, or category
- **Job Location** (Non-hierarchical) - City, region, or location tags

## Admin Settings

- Enable/disable Jobs post type
- Menu position in admin
- Enable/disable archive page
- Public visibility toggle

## Frontend Usage

### Display Jobs with WP_Query
```php
$jobs = new WP_Query([
    'post_type' => 'job',
    'posts_per_page' => 10,
    'meta_key' => 'application_deadline',
    'orderby' => 'meta_value',
    'order' => 'ASC',
]);

if ($jobs->have_posts()) {
    while ($jobs->have_posts()) {
        $jobs->the_post();

        $start_date = get_field('start_date');
        $job_type = get_field('job_type');
        $location = get_the_terms(get_the_ID(), 'job_location');

        // Display job...
    }
    wp_reset_postdata();
}
```

### Filter by Taxonomy
```php
$remote_jobs = new WP_Query([
    'post_type' => 'job',
    'meta_query' => [
        [
            'key' => 'remote_work',
            'value' => 'remote',
            'compare' => '='
        ]
    ]
]);
```

## Notes for Developers

- This is an **example implementation** - adapt it to your needs
- For production use, consider adding custom admin columns
- Add custom meta boxes if needed alongside ACF
- Consider adding REST API endpoints for headless usage
- Archive and single templates go in your theme: `archive-job.php`, `single-job.php`

## Removing the Jobs Post Type

If you don't need the Jobs post type:
1. Set `Enable Jobs` to `false` in admin settings
2. Or remove the Jobs-related code from Service.php entirely and add your own CPTs
