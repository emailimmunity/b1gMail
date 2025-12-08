<style>
.mf-abtesting {
    padding: 20px;
    background: #f5f5f5;
}
.mf-test-card {
    background: white;
    padding: 25px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
}
.mf-test-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}
.mf-test-status {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}
.mf-test-status.draft { background: #f5f5f5; color: #999; }
.mf-test-status.running { background: #d4edda; color: #155724; }
.mf-test-status.paused { background: #fff3cd; color: #856404; }
.mf-test-status.completed { background: #cfe2ff; color: #084298; }
.mf-variants {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin: 20px 0;
}
.mf-variant {
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    padding: 20px;
}
.mf-variant.winner {
    border-color: #76B82A;
    background: #f0f9ff;
}
.mf-variant h4 {
    margin: 0 0 15px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.mf-variant-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-top: 15px;
}
.mf-stat {
    text-align: center;
}
.mf-stat-value {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}
.mf-stat-label {
    font-size: 12px;
    color: #999;
    margin-top: 5px;
}
.mf-actions {
    display: flex;
    gap: 10px;
    margin-top: 20px;
}
.mf-btn {
    padding: 10px 20px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
}
.mf-btn-start { background: #27AE60; color: white; }
.mf-btn-pause { background: #F39C12; color: white; }
.mf-btn-complete { background: #3498DB; color: white; }
.mf-btn-delete { background: #E74C3C; color: white; }
.mf-btn:hover { opacity: 0.8; transform: translateY(-2px); }
.mf-create-btn {
    background: #76B82A;
    color: white;
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    font-size: 16px;
}
.mf-modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.7);
    z-index: 9999;
    align-items: center;
    justify-content: center;
}
.mf-modal.active { display: flex; }
.mf-modal-content {
    background: white;
    padding: 30px;
    border-radius: 8px;
    max-width: 700px;
    width: 90%;
    max-height: 90vh;
    overflow-y: auto;
}
.mf-form-group {
    margin-bottom: 20px;
}
.mf-form-group label {
    display: block;
    font-weight: 600;
    margin-bottom: 8px;
}
.mf-form-group input,
.mf-form-group textarea {
    width: 100%;
    padding: 12px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
}
.mf-form-group textarea {
    min-height: 100px;
    resize: vertical;
}
.mf-progress-bar {
    height: 8px;
    background: #f5f5f5;
    border-radius: 4px;
    overflow: hidden;
    margin: 15px 0;
}
.mf-progress-fill {
    height: 100%;
    background: #76B82A;
    transition: width 0.3s;
}
</style>

<div class="mf-abtesting">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px;">
        <div>
            <h1>🧪 A/B Testing</h1>
            <p style="color: #666; margin: 0;">Teste verschiedene Varianten und optimiere deine Conversion-Rate</p>
        </div>
        <button class="mf-create-btn" onclick="openCreateModal()">
            ➕ Neuer Test
        </button>
    </div>
    
    {if $success}
        <div style="background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
            ✓ {$success}
        </div>
    {/if}
    
    <!-- Tests List -->
    {if $tests && count($tests) > 0}
        {foreach from=$tests item=test}
            <div class="mf-test-card">
                <div class="mf-test-header">
                    <div>
                        <h3 style="margin: 0 0 5px 0;">{$test.test_name}</h3>
                        <p style="margin: 0; color: #666;">{$test.description}</p>
                    </div>
                    <span class="mf-test-status {$test.status}">{$test.status}</span>
                </div>
                
                <!-- Progress Bar -->
                {assign var="total" value=$test.variant_a_participants + $test.variant_b_participants}
                {if $total > 0}
                <div>
                    <div style="display: flex; justify-content: space-between; font-size: 12px; color: #666; margin-bottom: 5px;">
                        <span>Teilnehmer: {$total}</span>
                        <span>Traffic Split: {$test.traffic_split}% B</span>
                    </div>
                    <div class="mf-progress-bar">
                        <div class="mf-progress-fill" style="width: {math equation="(x / (x + y)) * 100" x=$test.variant_a_participants y=$test.variant_b_participants}%"></div>
                    </div>
                </div>
                {/if}
                
                <!-- Variants -->
                <div class="mf-variants">
                    <!-- Variant A -->
                    <div class="mf-variant {if $test.winner == 'a'}winner{/if}">
                        <h4>
                            <span>🅰️ Variante A (Original)</span>
                            {if $test.winner == 'a'}<span style="color: #76B82A;">👑 WINNER</span>{/if}
                        </h4>
                        <div class="mf-variant-stats">
                            <div class="mf-stat">
                                <div class="mf-stat-value">{$test.variant_a_participants}</div>
                                <div class="mf-stat-label">Teilnehmer</div>
                            </div>
                            <div class="mf-stat">
                                <div class="mf-stat-value">{$test.variant_a_conversions}</div>
                                <div class="mf-stat-label">Conversions</div>
                            </div>
                            <div class="mf-stat">
                                <div class="mf-stat-value" style="color: #76B82A;">{$test.variant_a_rate}%</div>
                                <div class="mf-stat-label">Rate</div>
                            </div>
                        </div>
                        {if $test.status == 'completed' && !$test.winner}
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="test_id" value="{$test.id}">
                            <input type="hidden" name="winner" value="a">
                            <button type="submit" name="select_winner" class="mf-btn" style="width: 100%; background: #76B82A; color: white;">
                                👑 Als Winner wählen
                            </button>
                        </form>
                        {/if}
                    </div>
                    
                    <!-- Variant B -->
                    <div class="mf-variant {if $test.winner == 'b'}winner{/if}">
                        <h4>
                            <span>🅱️ Variante B (Test)</span>
                            {if $test.winner == 'b'}<span style="color: #76B82A;">👑 WINNER</span>{/if}
                        </h4>
                        <div class="mf-variant-stats">
                            <div class="mf-stat">
                                <div class="mf-stat-value">{$test.variant_b_participants}</div>
                                <div class="mf-stat-label">Teilnehmer</div>
                            </div>
                            <div class="mf-stat">
                                <div class="mf-stat-value">{$test.variant_b_conversions}</div>
                                <div class="mf-stat-label">Conversions</div>
                            </div>
                            <div class="mf-stat">
                                <div class="mf-stat-value" style="color: #3498DB;">{$test.variant_b_rate}%</div>
                                <div class="mf-stat-label">Rate</div>
                            </div>
                        </div>
                        {if $test.status == 'completed' && !$test.winner}
                        <form method="POST" style="margin-top: 15px;">
                            <input type="hidden" name="test_id" value="{$test.id}">
                            <input type="hidden" name="winner" value="b">
                            <button type="submit" name="select_winner" class="mf-btn" style="width: 100%; background: #3498DB; color: white;">
                                👑 Als Winner wählen
                            </button>
                        </form>
                        {/if}
                    </div>
                </div>
                
                <!-- Actions -->
                <div class="mf-actions">
                    {if $test.status == 'draft' || $test.status == 'paused'}
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="test_id" value="{$test.id}">
                        <input type="hidden" name="new_status" value="running">
                        <button type="submit" name="change_status" class="mf-btn mf-btn-start">▶️ Starten</button>
                    </form>
                    {/if}
                    
                    {if $test.status == 'running'}
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="test_id" value="{$test.id}">
                        <input type="hidden" name="new_status" value="paused">
                        <button type="submit" name="change_status" class="mf-btn mf-btn-pause">⏸️ Pausieren</button>
                    </form>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="test_id" value="{$test.id}">
                        <input type="hidden" name="new_status" value="completed">
                        <button type="submit" name="change_status" class="mf-btn mf-btn-complete">✓ Abschließen</button>
                    </form>
                    {/if}
                </div>
            </div>
        {/foreach}
    {else}
        <div class="mf-test-card" style="text-align: center; padding: 60px;">
            <div style="font-size: 48px; margin-bottom: 20px;">🧪</div>
            <h3 style="margin: 0 0 10px 0;">Noch keine A/B Tests</h3>
            <p style="color: #666; margin: 0 0 20px 0;">Erstelle deinen ersten Test und optimiere deine Conversion-Rate!</p>
            <button class="mf-create-btn" onclick="openCreateModal()">
                ➕ Ersten Test erstellen
            </button>
        </div>
    {/if}
</div>

<!-- Create Test Modal -->
<div class="mf-modal" id="create-modal">
    <div class="mf-modal-content">
        <h2 style="margin: 0 0 20px 0;">🧪 Neuer A/B Test</h2>
        <form method="POST">
            <div class="mf-form-group">
                <label>Test-Name *</label>
                <input type="text" name="test_name" required placeholder="z.B. Hero CTA Button Text">
            </div>
            
            <div class="mf-form-group">
                <label>Beschreibung</label>
                <textarea name="description" placeholder="Beschreibe kurz, was getestet wird..."></textarea>
            </div>
            
            <div class="mf-form-group">
                <label>🅰️ Variante A (Original) *</label>
                <textarea name="variant_a" required placeholder="Aktueller Text/Content..."></textarea>
            </div>
            
            <div class="mf-form-group">
                <label>🅱️ Variante B (Test) *</label>
                <textarea name="variant_b" required placeholder="Alternative Version..."></textarea>
            </div>
            
            <div class="mf-form-group">
                <label>Traffic Split für Variante B (%)</label>
                <input type="number" name="traffic_split" value="50" min="0" max="100" required>
                <p style="font-size: 12px; color: #999; margin: 5px 0 0 0;">
                    50% = gleichmäßige Verteilung
                </p>
            </div>
            
            <div style="display: flex; gap: 10px; justify-content: flex-end; margin-top: 30px;">
                <button type="button" onclick="closeCreateModal()" style="padding: 12px 24px; background: #999; color: white; border: none; border-radius: 6px; cursor: pointer;">
                    Abbrechen
                </button>
                <button type="submit" name="create_test" class="mf-create-btn">
                    ✓ Test erstellen
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openCreateModal() {
    document.getElementById('create-modal').classList.add('active');
}

function closeCreateModal() {
    document.getElementById('create-modal').classList.remove('active');
}

document.getElementById('create-modal').addEventListener('click', function(e) {
    if(e.target === this) closeCreateModal();
});
</script>
