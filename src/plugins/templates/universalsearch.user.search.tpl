<div class="universalsearch-container">
	<h2>🔍 Universal-Suche</h2>
	
	<form action="start.php?action=universalsearch" method="post" class="search-form">
		<div class="search-box">
			<input type="text" name="q" value="{$search_query|default:''|escape}" 
			       placeholder="{lng p="universalsearch_placeholder"}" 
			       class="search-input" 
			       id="universalsearch-input"
			       autocomplete="off"
			       required>
			
			<select name="type" class="search-type">
				<option value="all" {if $search_type=='all'}selected{/if}>Alles</option>
				<option value="emails" {if $search_type=='emails'}selected{/if}>E-Mails</option>
				<option value="files" {if $search_type=='files'}selected{/if}>Dateien</option>
				<option value="calendar" {if $search_type=='calendar'}selected{/if}>Kalender</option>
				<option value="contacts" {if $search_type=='contacts'}selected{/if}>Kontakte</option>
			</select>
			
			<button type="submit" class="search-button">
				<i class="fa fa-search"></i> Suchen
			</button>
		</div>
		
		<div id="autocomplete-results" class="autocomplete-dropdown"></div>
	</form>
	
	{if isset($search_results)}
		<div class="search-results">
			{if $search_results.hits.total.value > 0}
				<h3>{lng p="universalsearch_results"}: {$search_results.hits.total.value}</h3>
				
				{* Faceted Search Filters *}
				{if isset($search_results.aggregations)}
				<div class="facets">
					<h4>Filter:</h4>
					<div class="facet-group">
						<strong>Nach Typ:</strong>
						{foreach from=$search_results.aggregations.by_type.buckets item=bucket}
							<span class="facet-item">{$bucket.key} ({$bucket.doc_count})</span>
						{/foreach}
					</div>
				</div>
				{/if}
				
				{* Results *}
				<div class="results-list">
					{foreach from=$search_results.hits.hits item=hit}
						<div class="result-item">
							{assign var="source" value=$hit._source}
							
							{* E-Mail Result *}
							{if $source.content_type == 'email'}
								<div class="result-icon">📧</div>
								<div class="result-content">
									<h4>
										{if isset($hit.highlight.subject)}
											{$hit.highlight.subject.0|unescape}
										{else}
											{$source.subject|escape}
										{/if}
									</h4>
									<p class="result-meta">
										<strong>Von:</strong> {$source.from|escape} • 
										<strong>Datum:</strong> {$source.timestamp|date_format:"%d.%m.%Y %H:%M"} •
										<strong>Größe:</strong> {size bytes=$source.size}
									</p>
									{if isset($hit.highlight.body_text)}
										<p class="result-excerpt">...{$hit.highlight.body_text.0|unescape}...</p>
									{/if}
									<p class="result-actions">
										<a href="email.php?mode=show&id={$source.mailid}" class="btn-small">
											<i class="fa fa-envelope-open"></i> E-Mail öffnen
										</a>
									</p>
								</div>
							
							{* File Result *}
							{elseif $source.content_type == 'file'}
								<div class="result-icon">📁</div>
								<div class="result-content">
									<h4>{$source.filename|escape}</h4>
									<p class="result-meta">
										<strong>Typ:</strong> .{$source.extension} • 
										<strong>Größe:</strong> {size bytes=$source.size} •
										<strong>Datum:</strong> {$source.timestamp|date_format:"%d.%m.%Y"}
									</p>
									<p class="result-actions">
										<a href="webdisk.php?action=download&id={$source.fileid}" class="btn-small">
											<i class="fa fa-download"></i> Herunterladen
										</a>
									</p>
								</div>
							
							{* Calendar Result *}
							{elseif $source.content_type == 'calendar'}
								<div class="result-icon">📅</div>
								<div class="result-content">
									<h4>{$source.title|escape}</h4>
									<p class="result-meta">
										<strong>Datum:</strong> {$source.start_date|date_format:"%d.%m.%Y %H:%M"} •
										{if $source.location}<strong>Ort:</strong> {$source.location|escape}{/if}
									</p>
									{if isset($hit.highlight.description)}
										<p class="result-excerpt">...{$hit.highlight.description.0|unescape}...</p>
									{/if}
									<p class="result-actions">
										<a href="organizer.php?action=edit&id={$source.eventid}" class="btn-small">
											<i class="fa fa-calendar"></i> Termin ansehen
										</a>
									</p>
								</div>
							
							{* Contact Result *}
							{elseif $source.content_type == 'contact'}
								<div class="result-icon">👤</div>
								<div class="result-content">
									<h4>{$source.firstname|escape} {$source.lastname|escape}</h4>
									<p class="result-meta">
										{if $source.company}<strong>Firma:</strong> {$source.company|escape} • {/if}
										{if $source.email}<strong>E-Mail:</strong> {$source.email|escape} • {/if}
										{if $source.mobile}<strong>Mobil:</strong> {$source.mobile|escape}{/if}
									</p>
									<p class="result-actions">
										<a href="abook.php?action=edit&id={$source.contactid}" class="btn-small">
											<i class="fa fa-user"></i> Kontakt ansehen
										</a>
									</p>
								</div>
							{/if}
							
							<div class="result-score">
								<small>Relevanz: {$hit._score|number_format:2:",":"."}</small>
							</div>
						</div>
					{/foreach}
				</div>
			{else}
				<div class="alert alert-warning">
					<h4>{lng p="universalsearch_no_results"}</h4>
					<p>Versuchen Sie es mit anderen Suchbegriffen oder wählen Sie einen anderen Typ.</p>
				</div>
			{/if}
		</div>
	{/if}
</div>

<script>
// Autocomplete
const input = document.getElementById('universalsearch-input');
const dropdown = document.getElementById('autocomplete-results');

let autocompleteTimeout;

input.addEventListener('input', function() {
	clearTimeout(autocompleteTimeout);
	
	const query = this.value;
	if (query.length < 2) {
		dropdown.style.display = 'none';
		return;
	}
	
	autocompleteTimeout = setTimeout(function() {
		fetch('start.php?universalsearch_autocomplete=1&q=' + encodeURIComponent(query))
			.then(response => response.json())
			.then(data => {
				if (data.length > 0) {
					dropdown.innerHTML = data.map(item => 
						`<div class="autocomplete-item" data-text="${item.text}">
							<span class="item-icon">${getIcon(item.type)}</span>
							<span class="item-text">${item.text}</span>
							<span class="item-type">${item.type}</span>
						</div>`
					).join('');
					dropdown.style.display = 'block';
					
					// Click handler
					document.querySelectorAll('.autocomplete-item').forEach(item => {
						item.addEventListener('click', function() {
							input.value = this.dataset.text;
							dropdown.style.display = 'none';
							document.querySelector('form').submit();
						});
					});
				} else {
					dropdown.style.display = 'none';
				}
			});
	}, 300);
});

function getIcon(type) {
	const icons = {
		'email': '📧',
		'file': '📁',
		'calendar': '📅',
		'contact': '👤'
	};
	return icons[type] || '📄';
}

// Close on click outside
document.addEventListener('click', function(e) {
	if (!input.contains(e.target) && !dropdown.contains(e.target)) {
		dropdown.style.display = 'none';
	}
});
</script>

<style>
.universalsearch-container {
	max-width: 1200px;
	margin: 20px auto;
	padding: 20px;
}

.search-form {
	margin-bottom: 30px;
}

.search-box {
	display: flex;
	gap: 10px;
	margin-bottom: 10px;
	position: relative;
}

.search-input {
	flex: 1;
	padding: 12px 15px;
	border: 2px solid #ddd;
	border-radius: 6px;
	font-size: 16px;
}

.search-input:focus {
	border-color: #4CAF50;
	outline: none;
}

.search-type {
	padding: 12px;
	border: 2px solid #ddd;
	border-radius: 6px;
	font-size: 14px;
	min-width: 150px;
}

.search-button {
	padding: 12px 30px;
	background: #4CAF50;
	color: white;
	border: none;
	border-radius: 6px;
	font-weight: bold;
	cursor: pointer;
	font-size: 14px;
}

.search-button:hover {
	background: #45a049;
}

.autocomplete-dropdown {
	display: none;
	position: absolute;
	top: 100%;
	left: 0;
	right: 200px;
	background: white;
	border: 1px solid #ddd;
	border-radius: 6px;
	box-shadow: 0 4px 6px rgba(0,0,0,0.1);
	max-height: 300px;
	overflow-y: auto;
	z-index: 1000;
	margin-top: 5px;
}

.autocomplete-item {
	padding: 10px 15px;
	cursor: pointer;
	display: flex;
	align-items: center;
	gap: 10px;
	border-bottom: 1px solid #eee;
}

.autocomplete-item:hover {
	background: #f5f5f5;
}

.item-icon {
	font-size: 20px;
}

.item-text {
	flex: 1;
	font-weight: 500;
}

.item-type {
	color: #888;
	font-size: 12px;
	text-transform: uppercase;
}

.results-list {
	margin-top: 20px;
}

.result-item {
	background: white;
	border: 1px solid #ddd;
	border-radius: 8px;
	padding: 20px;
	margin-bottom: 15px;
	display: flex;
	gap: 15px;
	box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.result-item:hover {
	box-shadow: 0 4px 8px rgba(0,0,0,0.1);
}

.result-icon {
	font-size: 32px;
	min-width: 50px;
	text-align: center;
}

.result-content {
	flex: 1;
}

.result-content h4 {
	margin: 0 0 10px 0;
	color: #333;
}

.result-meta {
	color: #666;
	font-size: 13px;
	margin: 5px 0;
}

.result-excerpt {
	background: #fffacd;
	padding: 10px;
	border-left: 3px solid #ffd700;
	margin: 10px 0;
	font-size: 14px;
}

.result-excerpt em {
	background: #ffeb3b;
	font-style: normal;
	font-weight: bold;
}

.result-actions {
	margin-top: 10px;
}

.btn-small {
	display: inline-block;
	padding: 6px 12px;
	background: #007bff;
	color: white;
	text-decoration: none;
	border-radius: 4px;
	font-size: 13px;
}

.btn-small:hover {
	background: #0056b3;
}

.result-score {
	text-align: right;
	color: #999;
	font-size: 12px;
}

.facets {
	background: #f9f9f9;
	padding: 15px;
	border-radius: 6px;
	margin-bottom: 20px;
}

.facet-group {
	margin: 10px 0;
}

.facet-item {
	display: inline-block;
	background: #e0e0e0;
	padding: 5px 10px;
	border-radius: 4px;
	margin-right: 5px;
	font-size: 13px;
	cursor: pointer;
}

.facet-item:hover {
	background: #d0d0d0;
}

.list {
	width: 100%;
	border-collapse: collapse;
	margin-top: 20px;
}

.list th {
	background: #f0f0f0;
	padding: 8px;
	font-weight: bold;
	border-bottom: 2px solid #ddd;
}

.list td {
	padding: 8px;
	border-bottom: 1px solid #eee;
}

.td1 { background: #f9f9f9; }
.td2 { background: #fff; }
</style>

