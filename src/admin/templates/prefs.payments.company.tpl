<form action="prefs.payments.php?action=company&save=true&sid={$sid}" method="post" onsubmit="spin(this)">

{if !$bm_prefs.invoice_company_name || !$bm_prefs.invoice_tax_number}
<div class="alert alert-danger" role="alert">
	<strong><i class="fa fa-exclamation-triangle"></i> {lng p="invoice_compliance_warning"}</strong>
</div>
{/if}

<fieldset>
	<legend>{lng p="invoice_company_data"}</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_company_name"} *</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_company_name" value="{$bm_prefs.invoice_company_name}" required>
			<small class="form-text text-muted">Vollständiger Firmenname (Pflichtangabe nach §14 UStG)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_company_line2"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_company_line2" value="{$bm_prefs.invoice_company_line2}">
			<small class="form-text text-muted">Optional: Abteilung, Zusatz, etc.</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_street"} *</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_street" value="{$bm_prefs.invoice_street}" required>
			<small class="form-text text-muted">Straße und Hausnummer</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_zip"} *</label>
		<div class="col-sm-3">
			<input type="text" class="form-control" name="invoice_zip" value="{$bm_prefs.invoice_zip}" required>
		</div>
		<label class="col-sm-2 col-form-label">{lng p="invoice_city"} *</label>
		<div class="col-sm-4">
			<input type="text" class="form-control" name="invoice_city" value="{$bm_prefs.invoice_city}" required>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_country"} *</label>
		<div class="col-sm-9">
			<select name="invoice_country" class="form-select" required>
				<option value="DE"{if $bm_prefs.invoice_country=='DE'} selected{/if}>Deutschland</option>
				<option value="AT"{if $bm_prefs.invoice_country=='AT'} selected{/if}>Österreich</option>
				<option value="CH"{if $bm_prefs.invoice_country=='CH'} selected{/if}>Schweiz</option>
				<option value="FR"{if $bm_prefs.invoice_country=='FR'} selected{/if}>Frankreich</option>
				<option value="NL"{if $bm_prefs.invoice_country=='NL'} selected{/if}>Niederlande</option>
				<option value="BE"{if $bm_prefs.invoice_country=='BE'} selected{/if}>Belgien</option>
				<option value="LU"{if $bm_prefs.invoice_country=='LU'} selected{/if}>Luxemburg</option>
				<option value="IT"{if $bm_prefs.invoice_country=='IT'} selected{/if}>Italien</option>
				<option value="ES"{if $bm_prefs.invoice_country=='ES'} selected{/if}>Spanien</option>
				<option value="PL"{if $bm_prefs.invoice_country=='PL'} selected{/if}>Polen</option>
			</select>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>{lng p="taxdata"} (Pflicht nach §14 UStG)</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_tax_number"} *</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_tax_number" value="{$bm_prefs.invoice_tax_number}" required>
			<small class="form-text text-muted">Steuernummer (Pflicht, wenn keine USt-IdNr. vorhanden)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_vat_id"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_vat_id" value="{$bm_prefs.invoice_vat_id}" placeholder="DE123456789">
			<small class="form-text text-muted">Umsatzsteuer-Identifikationsnummer (empfohlen für EU-Geschäfte)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-check-label">{lng p="invoice_is_small_business"}</label>
		<div class="col-sm-9">
			<div class="form-check">
				<input class="form-check-input" type="checkbox" name="invoice_is_small_business"{if $bm_prefs.invoice_is_small_business=='yes'} checked{/if}>
				<label class="form-check-label">
					Kleinunternehmerregelung nach §19 UStG anwenden
				</label>
			</div>
			<small class="form-text text-muted">Wenn aktiviert, wird keine Umsatzsteuer auf Rechnungen ausgewiesen</small>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>{lng p="contactdata"}</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_email"}</label>
		<div class="col-sm-9">
			<input type="email" class="form-control" name="invoice_email" value="{$bm_prefs.invoice_email}">
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_phone"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_phone" value="{$bm_prefs.invoice_phone}">
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_fax"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_fax" value="{$bm_prefs.invoice_fax}">
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_website"}</label>
		<div class="col-sm-9">
			<input type="url" class="form-control" name="invoice_website" value="{$bm_prefs.invoice_website}" placeholder="https://">
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>{lng p="businessinfo"}</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_ceo"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_ceo" value="{$bm_prefs.invoice_ceo}">
			<small class="form-text text-muted">Geschäftsführer (bei GmbH erforderlich)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_register_court"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_register_court" value="{$bm_prefs.invoice_register_court}" placeholder="z.B. Amtsgericht München">
			<small class="form-text text-muted">Registergericht (bei GmbH/AG erforderlich)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_register_number"}</label>
		<div class="col-sm-9">
			<input type="text" class="form-control" name="invoice_register_number" value="{$bm_prefs.invoice_register_number}" placeholder="z.B. HRB 123456">
			<small class="form-text text-muted">Handelsregisternummer (bei GmbH/AG erforderlich)</small>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>{lng p="paymentterms"}</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_payment_terms_days"}</label>
		<div class="col-sm-9">
			<input type="number" class="form-control" name="invoice_payment_terms_days" value="{$bm_prefs.invoice_payment_terms_days}" min="0" max="365">
			<small class="form-text text-muted">Standard-Zahlungsziel in Tagen (z.B. 14 Tage)</small>
		</div>
	</div>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_payment_terms_text"}</label>
		<div class="col-sm-9">
			<textarea class="form-control" name="invoice_payment_terms_text" rows="3">{$bm_prefs.invoice_payment_terms_text}</textarea>
			<small class="form-text text-muted">Zusätzliche Zahlungsbedingungen (optional)</small>
		</div>
	</div>
</fieldset>

<fieldset>
	<legend>{lng p="invoice_footer_text"}</legend>
	
	<div class="mb-3 row">
		<label class="col-sm-3 col-form-label">{lng p="invoice_footer_text"}</label>
		<div class="col-sm-9">
			<textarea class="form-control" name="invoice_footer_text" rows="4">{$bm_prefs.invoice_footer_text}</textarea>
			<small class="form-text text-muted">Fußzeile der Rechnung (z.B. Registerdaten, Bank, etc.)</small>
		</div>
	</div>
</fieldset>

<div class="text-end">
	<input class="btn btn-primary" type="submit" value="{lng p="save"}" />
</div>

</form>
