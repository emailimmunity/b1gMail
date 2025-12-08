{* Include Navigation *}
{include file='_navigation.tpl' currentPage='dashboard' pageTitle='Dashboard'}

<style>
.mf-dashboard {
    padding: 20px;
    background: #f5f5f5;
}
.mf-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}
.mf-stat-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    border-left: 4px solid {$theme.primary_color|default:'#76B82A'};
}
.mf-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}
.mf-stat-card .value {
    font-size: 36px;
    font-weight: bold;
    color: #333;
    margin: 10px 0;
}
.mf-stat-card .label {
    font-size: 12px;
    color: #999;
}
.mf-quick-actions {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    margin-bottom: 30px;
}
.mf-action-btn {
    display: inline-block;
    padding: 15px 25px;
    background: {$theme.primary_color|default:'#76B82A'};
    color: white;
    text-decoration: none;
    border-radius: 6px;
    text-align: center;
    font-weight: 500;
    transition: all 0.3s;
}
.mf-action-btn:hover {
    background: {$theme.primary_dark|default:'#5D9321'};
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.2);
}
.mf-recent-activity {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.mf-recent-activity h2 {
    margin: 0 0 20px 0;
    font-size: 20px;
    color: #333;
}
.mf-activity-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}
.mf-activity-item:last-child {
    border-bottom: none;
}
.mf-activity-item .time {
    font-size: 12px;
    color: #999;
}
.mf-activity-item .action {
    font-size: 14px;
    color: #333;
    margin-top: 5px;
}
</style>

<div class="mf-dashboard">
    <h1>🎨 ModernFrontend CMS - Dashboard</h1>
    
    <!-- Statistics -->
    <div class="mf-stats-grid">
        <div class="mf-stat-card">
            <h3>📝 Content Items</h3>
            <div class="value">{$stats.content_count|default:0}</div>
            <div class="label">Editable content blocks</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>🖼️ Media Files</h3>
            <div class="value">{$stats.media_count|default:0}</div>
            <div class="label">Images, videos, files</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>📄 Published Pages</h3>
            <div class="value">{$stats.pages_count|default:0}</div>
            <div class="label">Custom pages live</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>👁️ Pageviews (7d)</h3>
            <div class="value">{$stats.pageviews_7d|default:0}</div>
            <div class="label">Last 7 days</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>🧪 A/B Tests</h3>
            <div class="value">{$stats.ab_tests_running|default:0}</div>
            <div class="label">Currently running</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>✉️ Unread Messages</h3>
            <div class="value">{$stats.unread_messages|default:0}</div>
            <div class="label">Contact form submissions</div>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <h2>⚡ Quick Actions</h2>
    <div class="mf-quick-actions">
        <a href="{$pageURL}&page=content" class="mf-action-btn">
            📝 Edit Content
        </a>
        <a href="{$pageURL}&page=media" class="mf-action-btn">
            🖼️ Upload Media
        </a>
        <a href="{$pageURL}&page=theme" class="mf-action-btn">
            🎨 Customize Theme
        </a>
        <a href="{$pageURL}&page=pagebuilder" class="mf-action-btn">
            🏗️ Build Pages
        </a>
        <a href="{$pageURL}&page=packages" class="mf-action-btn">
            📦 Manage Packages
        </a>
        <a href="{$pageURL}&page=designs" class="mf-action-btn">
            🎭 Manage Designs
        </a>
        <a href="{$pageURL}&page=domains" class="mf-action-btn">
            🌐 Manage Domains
        </a>
        <a href="{$pageURL}&page=analytics" class="mf-action-btn">
            📊 View Analytics
        </a>
    </div>
    
    <!-- Recent Activity -->
    <div class="mf-recent-activity">
        <h2>📋 Recent Activity</h2>
        
        {if $activity && count($activity) > 0}
            {foreach from=$activity item=item}
                <div class="mf-activity-item">
                    <div class="time">{$item.updated_at}</div>
                    <div class="action">
                        Updated <strong>{$item.section}.{$item.key}</strong>
                    </div>
                </div>
            {/foreach}
        {else}
            <p style="color: #999; text-align: center; padding: 20px;">
                No recent activity
            </p>
        {/if}
    </div>
    
    <!-- System Info -->
    <div class="mf-recent-activity" style="margin-top: 30px;">
        <h2>ℹ️ System Information</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; font-weight: bold;">Plugin Version</td>
                <td style="padding: 10px;">{$smarty.const.MODERNFRONTEND_VERSION}</td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; font-weight: bold;">Primary Color</td>
                <td style="padding: 10px;">
                    <span style="display: inline-block; width: 20px; height: 20px; background: {$theme.primary_color|default:'#76B82A'}; border-radius: 4px; vertical-align: middle;"></span>
                    {$theme.primary_color|default:'#76B82A'}
                </td>
            </tr>
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 10px; font-weight: bold;">Site Title</td>
                <td style="padding: 10px;">{$theme.site_title|default:'aikQ Mail'}</td>
            </tr>
            <tr>
                <td style="padding: 10px; font-weight: bold;">Analytics Enabled</td>
                <td style="padding: 10px;">
                    {if $settings.analytics_enabled == '1'}
                        <span style="color: green;">✓ Yes</span>
                    {else}
                        <span style="color: red;">✗ No</span>
                    {/if}
                </td>
            </tr>
        </table>
    </div>
    
    <!-- Help -->
    <div class="mf-recent-activity" style="margin-top: 30px; background: #f0f9ff; border-left: 4px solid #3498DB;">
        <h2>💡 Getting Started</h2>
        <ol style="margin: 0; padding-left: 20px; line-height: 1.8;">
            <li><strong>Edit Content:</strong> Go to <a href="{$pageURL}&page=content">Content Editor</a> to change texts and descriptions</li>
            <li><strong>Upload Media:</strong> Use <a href="{$pageURL}&page=media">Media Library</a> to manage images and files</li>
            <li><strong>Customize Theme:</strong> Adjust colors and branding in <a href="{$pageURL}&page=theme">Theme Editor</a></li>
            <li><strong>Build Pages:</strong> Create custom pages with <a href="{$pageURL}&page=pagebuilder">Page Builder</a></li>
            <li><strong>Track Performance:</strong> Monitor visitors in <a href="{$pageURL}&page=analytics">Analytics</a></li>
        </ol>
    </div>
</div>
