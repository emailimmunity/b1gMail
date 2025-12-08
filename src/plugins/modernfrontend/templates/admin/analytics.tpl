<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<style>
.mf-analytics {
    padding: 20px;
    background: #f5f5f5;
}
.mf-stats-row {
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
}
.mf-stat-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #666;
    text-transform: uppercase;
}
.mf-stat-card .value {
    font-size: 36px;
    font-weight: bold;
    color: #333;
}
.mf-stat-card .change {
    font-size: 14px;
    margin-top: 5px;
}
.mf-stat-card .change.positive {
    color: #27AE60;
}
.mf-stat-card .change.negative {
    color: #E74C3C;
}
.mf-chart-container {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.mf-chart-container h2 {
    margin: 0 0 20px 0;
}
.mf-table-container {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}
.mf-table-container h2 {
    margin: 0 0 20px 0;
}
.mf-table {
    width: 100%;
    border-collapse: collapse;
}
.mf-table th {
    text-align: left;
    padding: 12px;
    background: #f5f5f5;
    font-weight: 600;
    border-bottom: 2px solid #e0e0e0;
}
.mf-table td {
    padding: 12px;
    border-bottom: 1px solid #f5f5f5;
}
.mf-table tr:hover {
    background: #f9f9f9;
}
.mf-filters {
    display: flex;
    gap: 10px;
    margin-bottom: 20px;
}
.mf-filter-btn {
    padding: 10px 20px;
    border: 2px solid #76B82A;
    background: white;
    color: #76B82A;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}
.mf-filter-btn.active {
    background: #76B82A;
    color: white;
}
.mf-filter-btn:hover {
    background: #76B82A;
    color: white;
}
</style>

<div class="mf-analytics">
    <h1>📊 Analytics Dashboard</h1>
    <p style="color: #666; margin-bottom: 30px;">
        Übersicht über Besucherstatistiken und Conversion-Daten
    </p>
    
    <!-- Date Range Filter -->
    <div class="mf-filters">
        <a href="{$pageURL}&page=analytics&days=7" class="mf-filter-btn {if $days == 7}active{/if}">
            Letzte 7 Tage
        </a>
        <a href="{$pageURL}&page=analytics&days=30" class="mf-filter-btn {if $days == 30}active{/if}">
            Letzte 30 Tage
        </a>
        <a href="{$pageURL}&page=analytics&days=90" class="mf-filter-btn {if $days == 90}active{/if}">
            Letzte 90 Tage
        </a>
    </div>
    
    <!-- Stats Cards -->
    <div class="mf-stats-row">
        <div class="mf-stat-card">
            <h3>👁️ Seitenaufrufe</h3>
            <div class="value">{$total_pageviews|number_format:0:",":"."}</div>
            <div class="change">Letzte {$days} Tage</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>👥 Eindeutige Besucher</h3>
            <div class="value">{$unique_visitors|number_format:0:",":"."}</div>
            <div class="change">Letzte {$days} Tage</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>✅ Conversions</h3>
            <div class="value">{$conversions|number_format:0:",":"."}</div>
            <div class="change">Letzte {$days} Tage</div>
        </div>
        
        <div class="mf-stat-card">
            <h3>📈 Conversion Rate</h3>
            <div class="value">{$conversion_rate}%</div>
            <div class="change {if $conversion_rate >= 2}positive{else}negative{/if}">
                {if $conversion_rate >= 2}✓ Gut{else}Verbesserungspotenzial{/if}
            </div>
        </div>
    </div>
    
    <!-- Pageviews Chart -->
    <div class="mf-chart-container">
        <h2>📈 Seitenaufrufe im Zeitverlauf</h2>
        <canvas id="pageviewsChart" height="80"></canvas>
    </div>
    
    <!-- Two Column Layout -->
    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 30px;">
        <!-- Device Breakdown -->
        <div class="mf-chart-container">
            <h2>📱 Gerätetypen</h2>
            <canvas id="devicesChart"></canvas>
        </div>
        
        <!-- Top Referrers -->
        <div class="mf-table-container">
            <h2>🔗 Top Referrer</h2>
            {if $top_referrers && count($top_referrers) > 0}
            <table class="mf-table">
                <thead>
                    <tr>
                        <th>Referrer</th>
                        <th style="text-align: right;">Besucher</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$top_referrers item=ref}
                    <tr>
                        <td style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">{$ref.referrer}</td>
                        <td style="text-align: right; font-weight: 600;">{$ref.count}</td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
            {else}
            <p style="text-align: center; color: #999; padding: 20px;">Keine Referrer-Daten vorhanden</p>
            {/if}
        </div>
    </div>
    
    <!-- Top Pages -->
    <div class="mf-table-container">
        <h2>📄 Beliebteste Seiten</h2>
        {if $top_pages && count($top_pages) > 0}
        <table class="mf-table">
            <thead>
                <tr>
                    <th>Seite</th>
                    <th style="text-align: right;">Aufrufe</th>
                    <th style="text-align: right;">Anteil</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$top_pages item=page}
                <tr>
                    <td>{$page.page_url}</td>
                    <td style="text-align: right; font-weight: 600;">{$page.views}</td>
                    <td style="text-align: right;">{math equation="round((x / y) * 100, 1)" x=$page.views y=$total_pageviews}%</td>
                </tr>
                {/foreach}
            </tbody>
        </table>
        {else}
        <p style="text-align: center; color: #999; padding: 20px;">Keine Daten vorhanden</p>
        {/if}
    </div>
    
    <!-- Export & Info -->
    <div style="background: #f0f9ff; border-left: 4px solid #3498DB; padding: 20px; border-radius: 8px;">
        <h3 style="margin: 0 0 10px 0;">💡 Hinweise</h3>
        <ul style="margin: 0; padding-left: 20px; line-height: 1.8;">
            <li>Analytics-Tracking ist aktiviert und erfasst alle Seitenaufrufe automatisch</li>
            <li>Daten werden in Echtzeit erfasst und sind sofort verfügbar</li>
            <li>IP-Adressen werden anonymisiert gespeichert (DSGVO-konform)</li>
            <li>Export-Funktion für detaillierte Analysen (Coming Soon)</li>
        </ul>
    </div>
</div>

<script>
// Pageviews Chart
const pageviewsData = {
    labels: [
        {foreach from=$pageviews_by_day item=day name=loop}
            '{$day.date}'{if !$smarty.foreach.loop.last},{/if}
        {/foreach}
    ],
    datasets: [{
        label: 'Seitenaufrufe',
        data: [
            {foreach from=$pageviews_by_day item=day name=loop}
                {$day.count}{if !$smarty.foreach.loop.last},{/if}
            {/foreach}
        ],
        borderColor: '#76B82A',
        backgroundColor: 'rgba(118, 184, 42, 0.1)',
        tension: 0.4,
        fill: true
    }]
};

new Chart(document.getElementById('pageviewsChart'), {
    type: 'line',
    data: pageviewsData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    }
});

// Devices Chart
const devicesData = {
    labels: ['Desktop', 'Mobile', 'Tablet'],
    datasets: [{
        data: [
            {$devices.desktop},
            {$devices.mobile},
            {$devices.tablet}
        ],
        backgroundColor: [
            '#76B82A',
            '#3498DB',
            '#F39C12'
        ]
    }]
};

new Chart(document.getElementById('devicesChart'), {
    type: 'doughnut',
    data: devicesData,
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
