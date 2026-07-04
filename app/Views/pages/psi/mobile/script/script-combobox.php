<script>
    // Shared text-escaping/highlighting used by every combobox's option template.
    function comboEscHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        }[c]));
    }

    function comboHighlight(text, query) {
        const str = String(text);
        if (!query) return comboEscHtml(str);
        const i = str.toLowerCase().indexOf(query);
        if (i === -1) return comboEscHtml(str);
        return comboEscHtml(str.slice(0, i)) +
            '<mark>' + comboEscHtml(str.slice(i, i + query.length)) + '</mark>' +
            comboEscHtml(str.slice(i + query.length));
    }

    // Turns a hidden <select> data pool (server-rendered <option> list) into
    // the plain {idx, label, material} item array a combobox expects.
    function comboItemsFromPool($pool) {
        return $pool.find('option').map(function() {
            return { idx: this.value, label: $(this).text().trim(), material: $(this).data('material') };
        }).get();
    }

    /**
     * Generic searchable combobox — shared behavior for the name, equipment-type
     * and equipment-id fields so they all look and behave identically.
     *
     * cfg:
     *   comboId, inputId, dropdownId, clearId (clearId optional)
     *   key(item)                          → unique identity, used to mark the selected row
     *   label(item)                        → text put into the input once selected
     *   matches(item, query)                → whether item stays in the filtered list
     *   renderOption(item, query, isSelected, i) → option row HTML
     *   emptyHtml                          → HTML shown when nothing matches (optional)
     *   onSelect(item)                      → called after a selection is made
     *   onClear()                           → called after the selection is cleared
     *
     * returns { setItems, setDisabled, setPlaceholder, clear }
     */
    function initCombobox(cfg) {
        const $combo = $('#' + cfg.comboId);
        const $input = $('#' + cfg.inputId);
        const $menu  = $('#' + cfg.dropdownId);
        const $list  = $menu.find('.combo-list');
        const $clear = cfg.clearId ? $('#' + cfg.clearId) : $();

        let items     = [];
        let filtered  = [];
        let activeIdx = -1;
        let selected  = null;
        let typed     = '';

        function renderList(rawQuery) {
            const query = (rawQuery || '').trim().toLowerCase();
            filtered = !query ? items.slice() : items.filter(it => cfg.matches(it, query));
            activeIdx = -1;

            if (!filtered.length) {
                $list.html(cfg.emptyHtml || '<div class="combo-empty">No matching option found</div>');
                return;
            }

            let html = '';
            filtered.forEach(function(it, i) {
                const isSelected = selected !== null && cfg.key(it) === cfg.key(selected);
                html += cfg.renderOption(it, query, isSelected, i);
            });
            $list.html(html);
        }

        function openMenu() {
            if ($input.prop('disabled')) return;
            renderList(typed);
            $menu.show();
            $combo.addClass('open');
            $input.attr('aria-expanded', 'true');
        }

        function closeMenu() {
            $menu.hide();
            $combo.removeClass('open');
            $input.attr('aria-expanded', 'false');
        }

        function applySelect(it, keepOpen) {
            selected = it;
            // Keep `typed` mirroring the input's new value (not '') so that if the
            // caller reopens the menu right after (auto-select-while-typing), it
            // re-filters against the matched label instead of flashing the full list.
            typed = cfg.label(it);
            $input.val(cfg.label(it)).removeClass('is-invalid');
            if ($clear.length) $clear.show();
            cfg.onSelect(it);
            if (!keepOpen) closeMenu();
        }

        function clearSelection() {
            selected = null;
            $input.val('');
            if ($clear.length) $clear.hide();
            if (cfg.onClear) cfg.onClear();
        }

        function setActive(idx) {
            activeIdx = idx;
            const $opts = $list.find('.combo-option');
            $opts.removeClass('active');
            if (idx >= 0) {
                const el = $opts.eq(idx).addClass('active')[0];
                if (el) el.scrollIntoView({ block: 'nearest' });
            }
        }

        $input.on('focus click', openMenu);

        $input.on('input', function() {
            typed = $(this).val();
            if ($clear.length) $clear.toggle(typed.length > 0);

            const exact = items.find(it => cfg.label(it).toLowerCase() === typed.trim().toLowerCase());
            if (exact) {
                applySelect(exact, true);
            } else if (selected !== null) {
                clearSelection();
            }
            openMenu();
        });

        $input.on('keydown', function(e) {
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!$menu.is(':visible')) return openMenu();
                setActive(Math.min(activeIdx + 1, filtered.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive(Math.max(activeIdx - 1, 0));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (activeIdx >= 0 && filtered[activeIdx]) applySelect(filtered[activeIdx]);
                else if (filtered.length === 1) applySelect(filtered[0]);
            } else if (e.key === 'Escape') {
                closeMenu();
            }
        });

        // mousedown (not click) so the input doesn't blur before selection registers
        $menu.on('mousedown', '.combo-option', function(e) {
            e.preventDefault();
            const it = filtered[$(this).data('i')];
            if (it) applySelect(it);
        });

        if ($clear.length) {
            $clear.on('click', function() {
                clearSelection();
                $input.trigger('focus');
            });
        }

        $(document).on('click', function(e) {
            if (!$(e.target).closest('#' + cfg.comboId).length) closeMenu();
        });

        return {
            setItems: function(newItems) { items = newItems || []; },
            setDisabled: function(disabled) {
                $input.prop('disabled', disabled);
                if (disabled) closeMenu();
            },
            setPlaceholder: function(text) { $input.attr('placeholder', text); },
            clear: function() { clearSelection(); }
        };
    }

    /**
     * Delegated combobox — for fields that can be rendered more than once at a
     * time (e.g. one trip form per open shift) and get injected/replaced via
     * AJAX. Unlike initCombobox (single, id-based instance bound once),
     * this binds via event delegation on document and keeps per-instance
     * state on the wrapper element's jQuery .data(), so it works for any
     * number of copies without re-initialization.
     *
     * cfg (classes, not ids — comboClass is the wrapper div's class):
     *   comboClass, inputClass, dropdownClass, clearClass (optional), valueClass (optional)
     *   valueClass → class of a hidden input, a SIBLING of the combo wrapper,
     *     that the engine keeps in sync automatically (.val() + trigger('change'))
     *   key(item), label(item), matches(item, query), renderOption(item, query, isSelected, i)
     *   emptyHtml, onSelect($combo, item) (optional), onClear($combo) (optional)
     *
     * returns { setItems($combo, items), selectByKey($combo, key), clear($combo),
     *           setDisabled($combo, disabled), setPlaceholder($combo, text) }
     */
    function initDelegatedCombobox(cfg) {
        const sel = {
            combo:    '.' + cfg.comboClass,
            input:    '.' + cfg.inputClass,
            dropdown: '.' + cfg.dropdownClass,
            clear:    cfg.clearClass ? '.' + cfg.clearClass : null,
            value:    cfg.valueClass ? '.' + cfg.valueClass : null,
        };

        function state($combo) {
            let st = $combo.data('comboState');
            if (!st) {
                st = { items: [], filtered: [], activeIdx: -1, selected: null, typed: '' };
                $combo.data('comboState', st);
            }
            return st;
        }

        function renderList($combo, rawQuery) {
            const st = state($combo);
            const query = (rawQuery || '').trim().toLowerCase();
            st.filtered = !query ? st.items.slice() : st.items.filter(it => cfg.matches(it, query));
            st.activeIdx = -1;

            const $list = $combo.find(sel.dropdown).find('.combo-list');
            if (!st.filtered.length) {
                $list.html(cfg.emptyHtml || '<div class="combo-empty">No matching option found</div>');
                return;
            }
            let html = '';
            st.filtered.forEach(function(it, i) {
                const isSelected = st.selected !== null && cfg.key(it) === cfg.key(st.selected);
                html += cfg.renderOption(it, query, isSelected, i);
            });
            $list.html(html);
        }

        function openMenu($combo) {
            if ($combo.find(sel.input).prop('disabled')) return;
            renderList($combo, state($combo).typed);
            $combo.find(sel.dropdown).show();
            $combo.addClass('open');
            $combo.find(sel.input).attr('aria-expanded', 'true');
        }

        function closeMenu($combo) {
            $combo.find(sel.dropdown).hide();
            $combo.removeClass('open');
            $combo.find(sel.input).attr('aria-expanded', 'false');
        }

        function applySelect($combo, it, keepOpen) {
            const st = state($combo);
            st.selected = it;
            // Keep `typed` mirroring the input's new value (not '') so that if the
            // caller reopens the menu right after (auto-select-while-typing), it
            // re-filters against the matched label instead of flashing the full list.
            st.typed = cfg.label(it);
            $combo.find(sel.input).val(cfg.label(it)).removeClass('is-invalid');
            if (sel.clear) $combo.find(sel.clear).show();
            if (sel.value) $combo.siblings(sel.value).val(cfg.key(it)).trigger('change');
            if (cfg.onSelect) cfg.onSelect($combo, it);
            if (!keepOpen) closeMenu($combo);
        }

        function clearSelection($combo) {
            const st = state($combo);
            st.selected = null;
            $combo.find(sel.input).val('');
            if (sel.clear) $combo.find(sel.clear).hide();
            if (sel.value) $combo.siblings(sel.value).val('').trigger('change');
            if (cfg.onClear) cfg.onClear($combo);
        }

        function setActive($combo, idx) {
            const st = state($combo);
            st.activeIdx = idx;
            const $opts = $combo.find(sel.dropdown).find('.combo-option');
            $opts.removeClass('active');
            if (idx >= 0) {
                const el = $opts.eq(idx).addClass('active')[0];
                if (el) el.scrollIntoView({ block: 'nearest' });
            }
        }

        $(document).on('focus click', sel.combo + ' ' + sel.input, function() {
            openMenu($(this).closest(sel.combo));
        });

        $(document).on('input', sel.combo + ' ' + sel.input, function() {
            const $combo = $(this).closest(sel.combo);
            const st = state($combo);
            st.typed = $(this).val();
            if (sel.clear) $combo.find(sel.clear).toggle(st.typed.length > 0);

            const exact = st.items.find(it => cfg.label(it).toLowerCase() === st.typed.trim().toLowerCase());
            if (exact) {
                applySelect($combo, exact, true);
            } else if (st.selected !== null) {
                clearSelection($combo);
            }
            openMenu($combo);
        });

        $(document).on('keydown', sel.combo + ' ' + sel.input, function(e) {
            const $combo = $(this).closest(sel.combo);
            const st = state($combo);
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!$combo.find(sel.dropdown).is(':visible')) return openMenu($combo);
                setActive($combo, Math.min(st.activeIdx + 1, st.filtered.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                setActive($combo, Math.max(st.activeIdx - 1, 0));
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (st.activeIdx >= 0 && st.filtered[st.activeIdx]) applySelect($combo, st.filtered[st.activeIdx]);
                else if (st.filtered.length === 1) applySelect($combo, st.filtered[0]);
            } else if (e.key === 'Escape') {
                closeMenu($combo);
            }
        });

        // mousedown (not click) so the input doesn't blur before selection registers
        $(document).on('mousedown', sel.combo + ' ' + sel.dropdown + ' .combo-option', function(e) {
            e.preventDefault();
            const $combo = $(this).closest(sel.combo);
            const st = state($combo);
            const it = st.filtered[$(this).data('i')];
            if (it) applySelect($combo, it);
        });

        if (sel.clear) {
            $(document).on('click', sel.combo + ' ' + sel.clear, function() {
                const $combo = $(this).closest(sel.combo);
                clearSelection($combo);
                $combo.find(sel.input).trigger('focus');
            });
        }

        $(document).on('click', function(e) {
            $(sel.combo + '.open').each(function() {
                if (!$(e.target).closest(sel.combo).is($(this))) closeMenu($(this));
            });
        });

        return {
            setItems: function($combo, items) { state($combo).items = items || []; },
            selectByKey: function($combo, key) {
                if (key === null || key === undefined || key === '') return clearSelection($combo);
                const it = state($combo).items.find(x => String(cfg.key(x)) === String(key));
                if (it) applySelect($combo, it, true);
                else clearSelection($combo);
            },
            clear: function($combo) { clearSelection($combo); },
            setDisabled: function($combo, disabled) {
                $combo.find(sel.input).prop('disabled', disabled);
                if (disabled) closeMenu($combo);
            },
            setPlaceholder: function($combo, text) { $combo.find(sel.input).attr('placeholder', text); }
        };
    }
</script>
