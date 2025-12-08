<?php
/**
 * ModernFrontend CMS - Language Switcher
 * Frontend-Komponente für Sprachumschaltung
 */

class LanguageSwitcher
{
	private $languageDetector;
	private $baseUrl;
	
	public function __construct()
	{
		require_once(B1GMAIL_DIR . 'plugins/modernfrontend/modules/languages/LanguageDetector.class.php');
		
		$this->languageDetector = new LanguageDetector();
		$this->baseUrl = B1GMAIL_REL;
	}
	
	/**
	 * Dropdown-Style Switcher
	 */
	public function renderDropdown($includeFlags = true)
	{
		$available = $this->languageDetector->getAvailableLanguages();
		$current = $this->languageDetector->getCurrentLanguageCode();
		
		$html = '<div class="mf-language-switcher mf-dropdown">';
		$html .= '<select onchange="window.location.href=this.value;" class="mf-lang-select">';
		
		foreach($available as $lang) {
			$selected = ($lang['code'] == $current) ? ' selected' : '';
			$url = $this->languageDetector->getLanguageUrl($lang['code']);
			
			$html .= '<option value="' . htmlspecialchars($url) . '"' . $selected . '>';
			
			if($includeFlags && !empty($lang['flag_icon'])) {
				$html .= $lang['flag_icon'] . ' ';
			}
			
			$html .= htmlspecialchars($lang['name']);
			$html .= ' (' . strtoupper($lang['code']) . ')';
			$html .= '</option>';
		}
		
		$html .= '</select>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Button-Style Switcher
	 */
	public function renderButtons($includeFlags = true, $showFullName = false)
	{
		$available = $this->languageDetector->getAvailableLanguages();
		$current = $this->languageDetector->getCurrentLanguageCode();
		
		$html = '<div class="mf-language-switcher mf-buttons">';
		
		foreach($available as $lang) {
			$active = ($lang['code'] == $current) ? ' active' : '';
			$url = $this->languageDetector->getLanguageUrl($lang['code']);
			
			$html .= '<a href="' . htmlspecialchars($url) . '" class="mf-lang-btn' . $active . '">';
			
			if($includeFlags && !empty($lang['flag_icon'])) {
				$html .= '<span class="mf-flag">' . $lang['flag_icon'] . '</span> ';
			}
			
			if($showFullName) {
				$html .= htmlspecialchars($lang['name']);
			} else {
				$html .= strtoupper($lang['code']);
			}
			
			$html .= '</a>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Flag-Only Switcher
	 */
	public function renderFlags()
	{
		$available = $this->languageDetector->getAvailableLanguages();
		$current = $this->languageDetector->getCurrentLanguageCode();
		
		$html = '<div class="mf-language-switcher mf-flags">';
		
		foreach($available as $lang) {
			$active = ($lang['code'] == $current) ? ' active' : '';
			$url = $this->languageDetector->getLanguageUrl($lang['code']);
			
			$html .= '<a href="' . htmlspecialchars($url) . '" class="mf-flag-btn' . $active . '" title="' . htmlspecialchars($lang['name']) . '">';
			
			if(!empty($lang['flag_icon'])) {
				$html .= $lang['flag_icon'];
			} else {
				$html .= strtoupper($lang['code']);
			}
			
			$html .= '</a>';
		}
		
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Mobile-Optimized Switcher
	 */
	public function renderMobile()
	{
		$available = $this->languageDetector->getAvailableLanguages();
		$current = $this->languageDetector->getCurrentLanguage();
		
		$html = '<div class="mf-language-switcher mf-mobile">';
		$html .= '<button class="mf-lang-toggle" onclick="this.parentElement.classList.toggle(\'open\')">';
		
		if($current && !empty($current['flag_icon'])) {
			$html .= $current['flag_icon'] . ' ';
		}
		
		$html .= htmlspecialchars($current ? $current['name'] : 'Language');
		$html .= ' <span class="mf-arrow">▼</span>';
		$html .= '</button>';
		
		$html .= '<div class="mf-lang-menu">';
		
		foreach($available as $lang) {
			$url = $this->languageDetector->getLanguageUrl($lang['code']);
			
			$html .= '<a href="' . htmlspecialchars($url) . '" class="mf-lang-item">';
			
			if(!empty($lang['flag_icon'])) {
				$html .= '<span class="mf-flag">' . $lang['flag_icon'] . '</span> ';
			}
			
			$html .= htmlspecialchars($lang['name']);
			$html .= '</a>';
		}
		
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * CSS für Switcher
	 */
	public function getCSS()
	{
		return '
<style>
.mf-language-switcher {
	display: inline-block;
	position: relative;
}

/* Dropdown Style */
.mf-language-switcher.mf-dropdown .mf-lang-select {
	padding: 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	background: white;
	cursor: pointer;
	font-size: 14px;
}

/* Button Style */
.mf-language-switcher.mf-buttons {
	display: flex;
	gap: 5px;
}
.mf-language-switcher.mf-buttons .mf-lang-btn {
	padding: 8px 12px;
	border: 1px solid #ddd;
	border-radius: 4px;
	background: white;
	text-decoration: none;
	color: #333;
	transition: all 0.2s;
	font-size: 14px;
}
.mf-language-switcher.mf-buttons .mf-lang-btn:hover {
	background: #f0f0f0;
}
.mf-language-switcher.mf-buttons .mf-lang-btn.active {
	background: #76B82A;
	color: white;
	border-color: #76B82A;
}

/* Flag Style */
.mf-language-switcher.mf-flags {
	display: flex;
	gap: 8px;
}
.mf-language-switcher.mf-flags .mf-flag-btn {
	display: inline-block;
	font-size: 24px;
	text-decoration: none;
	opacity: 0.6;
	transition: opacity 0.2s;
}
.mf-language-switcher.mf-flags .mf-flag-btn:hover {
	opacity: 0.8;
}
.mf-language-switcher.mf-flags .mf-flag-btn.active {
	opacity: 1;
}

/* Mobile Style */
.mf-language-switcher.mf-mobile {
	position: relative;
}
.mf-language-switcher.mf-mobile .mf-lang-toggle {
	padding: 10px 15px;
	border: 1px solid #ddd;
	border-radius: 4px;
	background: white;
	cursor: pointer;
	font-size: 14px;
	display: flex;
	align-items: center;
	gap: 5px;
}
.mf-language-switcher.mf-mobile .mf-lang-menu {
	display: none;
	position: absolute;
	top: 100%;
	left: 0;
	right: 0;
	background: white;
	border: 1px solid #ddd;
	border-radius: 4px;
	margin-top: 5px;
	box-shadow: 0 2px 8px rgba(0,0,0,0.1);
	z-index: 1000;
}
.mf-language-switcher.mf-mobile.open .mf-lang-menu {
	display: block;
}
.mf-language-switcher.mf-mobile .mf-lang-item {
	display: block;
	padding: 10px 15px;
	text-decoration: none;
	color: #333;
	transition: background 0.2s;
}
.mf-language-switcher.mf-mobile .mf-lang-item:hover {
	background: #f0f0f0;
}
.mf-language-switcher.mf-mobile .mf-arrow {
	margin-left: auto;
	font-size: 10px;
}

@media (max-width: 768px) {
	.mf-language-switcher.mf-buttons {
		flex-wrap: wrap;
	}
}
</style>
		';
	}
	
	/**
	 * Komplettes Switcher-Paket (CSS + HTML)
	 */
	public function render($style = 'dropdown', $includeFlags = true, $showFullName = false)
	{
		$html = $this->getCSS();
		
		switch($style) {
			case 'buttons':
				$html .= $this->renderButtons($includeFlags, $showFullName);
				break;
			case 'flags':
				$html .= $this->renderFlags();
				break;
			case 'mobile':
				$html .= $this->renderMobile();
				break;
			default:
				$html .= $this->renderDropdown($includeFlags);
				break;
		}
		
		return $html;
	}
}
?>
