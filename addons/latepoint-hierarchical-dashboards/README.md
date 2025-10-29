# LatePoint Hierarchical Dashboards Add-on

This add-on extends LatePoint with a hierarchy of dashboards and roles tailored to account administrators, supervisors, service providers, and client organizations/individuals. It provides:

- Custom WordPress roles with LatePoint-specific capabilities.
- A front-end dashboard endpoint (`/latepoint-hub`) with switchable sections for agenda, clients, payments, reports, and more.
- Shortcodes for embedding the hierarchy navigation on any page.
- A base UI built with lightweight CSS/JS placeholders that can be connected to LatePoint's APIs.

## Installation

1. Copy the `latepoint-hierarchical-dashboards` folder into `wp-content/plugins/` on your WordPress site.
2. Activate **LatePoint Hierarchical Dashboards** from the WordPress Plugins screen.
3. Navigate to **Settings → Permalinks** and click **Save** once to ensure the `/latepoint-hub` endpoint is registered.
4. Assign your LatePoint users to the new roles:
   - `latepoint_account_admin`
   - `latepoint_supervisor`
   - `latepoint_service_provider`
   - `latepoint_client_pf`
   - `latepoint_client_pj`

## Usage

- Visit `https://example.com/latepoint-hub` while logged in to see the unified dashboard.
- Alternatively, create a page with the `[latepoint_hierarchy_dashboard]` shortcode to embed the interface.
- Replace the placeholder templates in `includes/templates/section-*.php` with LatePoint data using the plugin's PHP helpers or REST API.

## Extending the add-on

- Add new capabilities to `LatepointHierarchicalDashboards\Roles::CAPABILITIES` and include them in role definitions as needed.
- Register additional sections by updating `LatepointHierarchicalDashboards\Hierarchy::get_sections()` and providing matching templates.
- Hook into LatePoint filters/actions (e.g., `latepoint/agents/query_args`) inside the section templates to render contextual information per role.

## Development

The assets are plain CSS/JS files loaded only on the hierarchy endpoint. If you prefer a build step, enqueue your compiled assets from `LatepointHierarchicalDashboards\Assets`.

