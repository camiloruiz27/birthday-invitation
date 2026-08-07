<script>
    const loadingOverlay = document.querySelector('[data-loading-overlay]');
    const loadingTitle = document.querySelector('[data-loading-title]');
    const loadingMessage = document.querySelector('[data-loading-message]');
    const tabs = Array.from(document.querySelectorAll('[data-day-tab]'));
    const panels = Array.from(document.querySelectorAll('[data-day-panel]'));
    const activityCards = Array.from(document.querySelectorAll('[data-activity-card]'));
    const contextDay = document.querySelector('[data-context-day]');
    const contextLevel = document.querySelector('[data-context-level]');
    const contextLocation = document.querySelector('[data-context-location]');
    const contextActivity = document.querySelector('[data-context-activity]');
    const gameOutput = document.querySelector('[data-game-output]');
    const assistantUrl = '{{ route('couple-experience.assistant') }}';
    const savedDay = window.localStorage.getItem('coupleExperienceDay') || '1';
    const classicDisplay = document.querySelector('[data-classic-display]');
    const classicState = document.querySelector('[data-classic-state]');
    const classicStart = document.querySelector('[data-classic-start]');
    const classicPause = document.querySelector('[data-classic-pause]');
    const classicReset = document.querySelector('[data-classic-reset]');
    const expressPhase = document.querySelector('[data-express-phase]');
    const expressDisplay = document.querySelector('[data-express-display]');
    const expressRound = document.querySelector('[data-express-round]');
    const expressStart = document.querySelector('[data-express-start]');
    const expressPause = document.querySelector('[data-express-pause]');
    const expressReset = document.querySelector('[data-express-reset]');
    const soundToggle = document.querySelector('[data-sound-toggle]');
    const levelSixOptionsScript = document.querySelector('[data-level-six-options]');
    const levelSixTarget = document.querySelector('[data-level-six-target]');
    const levelSixType = document.querySelector('[data-level-six-type]');
    const levelSixLevel = document.querySelector('[data-level-six-level]');
    const levelSixPlayer = document.querySelector('[data-level-six-player]');
    const levelSixProgress = document.querySelector('[data-level-six-progress]');
    const levelSixCount = document.querySelector('[data-level-six-count]');
    const levelSixTitle = document.querySelector('[data-level-six-title]');
    const levelSixDescription = document.querySelector('[data-level-six-description]');
    const levelSixDetailWrap = document.querySelector('[data-level-six-detail-wrap]');
    const levelSixToggle = document.querySelector('[data-level-six-toggle]');
    const levelSixDuration = document.querySelector('[data-level-six-duration]');
    const levelSixNext = document.querySelector('[data-level-six-next]');
    const levelSixNextSummary = document.querySelector('[data-level-six-next-summary]');
    const levelSixHistory = document.querySelector('[data-level-six-history]');
    const levelSixReset = document.querySelector('[data-level-six-reset]');
    const levelSixTimerDisplay = document.querySelector('[data-level-six-timer-display]');
    const levelSixTimerState = document.querySelector('[data-level-six-timer-state]');
    const levelSixTimerInput = document.querySelector('[data-level-six-timer-input]');
    const levelSixTimerStart = document.querySelector('[data-level-six-timer-start]');
    const levelSixTimerPause = document.querySelector('[data-level-six-timer-pause]');
    const levelSixTimerReset = document.querySelector('[data-level-six-timer-reset]');
    let classicInterval = null;
    let classicSeconds = 0;
    let expressInterval = null;
    let expressSeconds = 45;
    let expressIsChange = false;
    let expressRoundCount = 1;
    let soundEnabled = window.localStorage.getItem('coupleTimerSound') !== 'off';
    let audioContext = null;
    let levelSixOptions = {};
    let levelSixRemaining = {};
    let levelSixPlayed = [];
    let levelSixCurrentLevel = 2;
    let levelSixCurrentPlayer = 'Camilo';
    let levelSixLevelTurns = 0;
    let levelSixDetailVisible = false;
    let levelSixTimerInterval = null;
    let levelSixTimerSeconds = 60;
    const levelSixLevelRules = {
        2: { next: 3, limit: 7 },
        3: { next: 4, limit: 6 },
        4: { next: 5, limit: 5 },
        5: { next: null, limit: null },
    };

    function showLoading(title = 'Procesando', message = 'Un momento, estamos guardando los cambios.') {
        if (!loadingOverlay) return;
        if (loadingTitle) loadingTitle.textContent = title;
        if (loadingMessage) loadingMessage.textContent = message;
        loadingOverlay.classList.remove('hidden');
        loadingOverlay.classList.add('flex');
    }

    function hideLoading() {
        if (!loadingOverlay) return;
        loadingOverlay.classList.add('hidden');
        loadingOverlay.classList.remove('flex');
    }

    showLoading('Cargando experiencia', 'Estamos preparando el tablero privado.');
    window.addEventListener('load', () => setTimeout(hideLoading, 250));
    window.addEventListener('pageshow', hideLoading);

    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', () => {
            const action = form.getAttribute('action') || '';
            if (action.includes('/login')) return showLoading('Validando acceso', 'Estamos verificando tus datos.');
            if (action.includes('/logout')) return showLoading('Cerrando sesion', 'Estamos saliendo de forma segura.');
            if (action.includes('/consent')) return showLoading('Guardando consentimiento', 'Estamos habilitando la experiencia.');
            if (action.includes('/ai/suggest')) return showLoading('Generando guia', 'La IA esta preparando una sugerencia para este momento.');
            if (action.includes('/answers')) return showLoading('Guardando respuesta', 'Estamos guardando esto para verlo juntos.');
            if (action.includes('/progress')) return showLoading('Actualizando progreso', 'Estamos marcando el reto.');
            showLoading();
        });
    });

    function activateDay(day) {
        tabs.forEach(tab => tab.dataset.active = tab.dataset.dayTab === day ? 'true' : 'false');
        panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.dayPanel !== day));
        if (contextDay) contextDay.value = day;
        window.localStorage.setItem('coupleExperienceDay', day);
        filterActivities();
    }

    function filterActivities() {
        if (!contextDay || !contextLevel || !contextLocation) return;

        const day = contextDay.value;
        const level = contextLevel.value;
        const location = contextLocation.value;
        let visibleCount = 0;

        activityCards.forEach(card => {
            const visible = card.dataset.day === day
                && card.dataset.level === level
                && (card.dataset.location === location || card.dataset.privacy === 'privado');
            card.classList.toggle('hidden', !visible);
            if (visible) visibleCount++;
        });

        if (visibleCount === 0) {
            activityCards.forEach(card => card.classList.toggle('hidden', !(card.dataset.day === day && card.dataset.level === level)));
        }
    }

    function showGameOutput(message) {
        if (!gameOutput) return;
        gameOutput.textContent = message;
        gameOutput.classList.remove('hidden');
    }

    function pickRandom(values) {
        return values[Math.floor(Math.random() * values.length)];
    }

    function cloneLevelSixOptions() {
        return JSON.parse(JSON.stringify(levelSixOptions));
    }

    function formatLevelSixCategory(category) {
        const labels = {
            exclusivas_hombre: 'Camilo',
            exclusivas_mujer: 'Isabella',
            compartidas: 'Compartida',
        };

        return labels[category] || 'Ruleta';
    }

    function levelSixTargetLabel(category) {
        if (category === 'exclusivas_hombre') return 'Para Camilo';
        if (category === 'exclusivas_mujer') return 'Para Isabella';
        return 'Para ambos';
    }

    function levelSixActionType(text) {
        const match = String(text || '').match(/^([^:]+):/);

        return match ? match[1].trim() : 'Actividad';
    }

    function levelSixKey(level = levelSixCurrentLevel) {
        return `nivel_${level}`;
    }

    function levelSixPlayerCategories() {
        return levelSixCurrentPlayer === 'Camilo'
            ? ['exclusivas_hombre', 'compartidas']
            : ['exclusivas_mujer', 'compartidas'];
    }

    function availableLevelSixCategories() {
        const level = levelSixRemaining[levelSixKey()] || {};
        let categories = levelSixPlayerCategories().filter(category => (level[category] || []).length);

        if (!categories.length && levelSixCurrentLevel === 5 && levelSixOptions[levelSixKey()]) {
            levelSixRemaining[levelSixKey()] = JSON.parse(JSON.stringify(levelSixOptions[levelSixKey()]));
            categories = levelSixPlayerCategories().filter(category => (levelSixRemaining[levelSixKey()][category] || []).length);
        }

        return categories;
    }

    function detectLevelSixSeconds(text) {
        const normalized = String(text || '').toLowerCase();
        const seconds = normalized.match(/(\d+)\s*segundo/);
        const minutes = normalized.match(/(\d+)\s*minuto/);

        if (minutes) {
            return Number(minutes[1]) * 60;
        }

        if (seconds) {
            return Number(seconds[1]);
        }

        return 60;
    }

    function updateLevelSixTimerDisplay() {
        if (levelSixTimerDisplay) levelSixTimerDisplay.textContent = formatMinutes(levelSixTimerSeconds);
        if (levelSixTimerInput) levelSixTimerInput.value = levelSixTimerSeconds;
    }

    function setLevelSixTimer(seconds) {
        if (levelSixTimerInterval) {
            clearInterval(levelSixTimerInterval);
            levelSixTimerInterval = null;
        }

        levelSixTimerSeconds = Math.max(5, Number(seconds) || 60);
        if (levelSixTimerState) levelSixTimerState.textContent = 'Listo';
        updateLevelSixTimerDisplay();
    }

    function updateLevelSixStatus() {
        const rule = levelSixLevelRules[levelSixCurrentLevel];
        const nextText = rule.limit ? `${levelSixLevelTurns} / ${rule.limit}` : 'Sin limite';

        if (levelSixLevel) levelSixLevel.textContent = `Nivel ${levelSixCurrentLevel}`;
        if (levelSixPlayer) levelSixPlayer.textContent = levelSixCurrentPlayer;
        if (levelSixProgress) levelSixProgress.textContent = nextText;
        if (levelSixCount) levelSixCount.textContent = String(levelSixPlayed.length);
        if (levelSixNext) levelSixNext.textContent = `${levelSixCurrentPlayer} presiona ahora.`;
        if (levelSixNextSummary) levelSixNextSummary.textContent = `${levelSixCurrentPlayer} presiona para sacar la siguiente actividad.`;
    }

    function renderLevelSixHistory() {
        if (!levelSixHistory) return;

        if (!levelSixPlayed.length) {
            levelSixHistory.textContent = 'Todavia no hay turnos.';
            return;
        }

        levelSixHistory.textContent = '';
        levelSixPlayed.slice(-6).reverse().forEach(item => {
            const entry = document.createElement('div');
            const category = document.createElement('p');
            const title = document.createElement('p');

            entry.className = 'rounded-md border border-stone-200 bg-white p-3';
            category.className = 'text-xs font-bold uppercase tracking-widest text-stone-500';
            title.className = 'mt-1 font-semibold text-stone-950';
            category.textContent = `Jugada ${item.turn} · Nivel ${item.level}`;
            title.textContent = `${levelSixTargetLabel(item.category)} · ${item.type} · ${item.seconds} segundos`;

            entry.append(category, title);
            levelSixHistory.appendChild(entry);
        });
    }

    function resetLevelSixRoulette() {
        levelSixRemaining = cloneLevelSixOptions();
        levelSixPlayed = [];
        levelSixCurrentLevel = 2;
        levelSixCurrentPlayer = 'Camilo';
        levelSixLevelTurns = 0;
        levelSixDetailVisible = false;
        if (levelSixTarget) levelSixTarget.textContent = 'Sin destino';
        if (levelSixType) levelSixType.textContent = 'Sin tipo';
        if (levelSixTitle) levelSixTitle.textContent = 'Presionen una opcion para empezar';
        if (levelSixDescription) levelSixDescription.textContent = '';
        if (levelSixDetailWrap) levelSixDetailWrap.classList.add('hidden');
        if (levelSixToggle) {
            levelSixToggle.classList.add('hidden');
            levelSixToggle.textContent = 'Mostrar actividad';
        }
        if (levelSixDuration) levelSixDuration.textContent = 'Sin tiempo detectado';
        setLevelSixTimer(60);
        updateLevelSixStatus();
        renderLevelSixHistory();
    }

    function advanceLevelSixIfNeeded() {
        const rule = levelSixLevelRules[levelSixCurrentLevel];

        if (!rule.limit || levelSixLevelTurns < rule.limit) {
            return;
        }

        levelSixCurrentLevel = rule.next;
        levelSixLevelTurns = 0;
    }

    function spinLevelSixRoulette() {
        const categories = availableLevelSixCategories();

        if (!categories.length) {
            if (levelSixTitle) levelSixTitle.textContent = 'Ya salieron todas las opciones disponibles';
            if (levelSixDescription) levelSixDescription.textContent = 'Reinicien la ruleta para volver a cargar esta categoria o revisen si este nivel tiene preguntas pendientes.';
            if (levelSixDuration) levelSixDuration.textContent = 'Sin tiempo detectado';
            return;
        }

        const level = levelSixKey();
        const player = levelSixCurrentPlayer;
        const selectedCategory = pickRandom(categories);
        const options = levelSixRemaining[level][selectedCategory];
        const optionIndex = Math.floor(Math.random() * options.length);
        const selectedText = options.splice(optionIndex, 1)[0];
        const seconds = detectLevelSixSeconds(selectedText);
        const type = levelSixActionType(selectedText);

        levelSixPlayed.push({
            turn: levelSixPlayed.length + 1,
            level: levelSixCurrentLevel,
            player,
            category: selectedCategory,
            text: selectedText,
            type,
            seconds,
        });

        levelSixLevelTurns++;
        levelSixDetailVisible = false;
        if (levelSixTarget) levelSixTarget.textContent = levelSixTargetLabel(selectedCategory);
        if (levelSixType) levelSixType.textContent = type;
        if (levelSixTitle) levelSixTitle.textContent = `Actividad para ${formatLevelSixCategory(selectedCategory)}`;
        if (levelSixDescription) levelSixDescription.textContent = selectedText;
        if (levelSixDetailWrap) levelSixDetailWrap.classList.add('hidden');
        if (levelSixToggle) {
            levelSixToggle.classList.remove('hidden');
            levelSixToggle.textContent = 'Mostrar actividad';
        }
        if (levelSixDuration) levelSixDuration.textContent = `${seconds} segundos`;
        setLevelSixTimer(seconds);

        levelSixCurrentPlayer = player === 'Camilo' ? 'Isabella' : 'Camilo';
        advanceLevelSixIfNeeded();
        updateLevelSixStatus();
        renderLevelSixHistory();
        playPhaseSound();
    }

    function formatMinutes(totalSeconds) {
        return `${String(Math.floor(totalSeconds / 60)).padStart(2, '0')}:${String(totalSeconds % 60).padStart(2, '0')}`;
    }

    function updateClassic() {
        if (classicDisplay) classicDisplay.textContent = formatMinutes(classicSeconds);
    }

    function updateExpress() {
        if (expressDisplay) expressDisplay.textContent = String(expressSeconds).padStart(2, '0');
        if (expressPhase) expressPhase.textContent = expressIsChange ? 'Cambio' : 'Turno';
        if (expressRound) expressRound.textContent = `Ronda ${expressRoundCount}`;
    }

    function updateSoundToggle() {
        if (!soundToggle) return;
        soundToggle.textContent = soundEnabled ? 'Sonido activado' : 'Sonido desactivado';
        soundToggle.classList.toggle('bg-stone-950', soundEnabled);
        soundToggle.classList.toggle('text-white', soundEnabled);
        soundToggle.classList.toggle('bg-white', !soundEnabled);
        soundToggle.classList.toggle('text-stone-900', !soundEnabled);
    }

    function getAudioContext() {
        if (!audioContext) {
            const AudioContextClass = window.AudioContext || window.webkitAudioContext;
            if (!AudioContextClass) return null;
            audioContext = new AudioContextClass();
        }
        if (audioContext.state === 'suspended') audioContext.resume();
        return audioContext;
    }

    function playTone(frequency = 660, duration = 0.12, volume = 0.08) {
        if (!soundEnabled) return;
        const context = getAudioContext();
        if (!context) return;
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const now = context.currentTime;
        oscillator.type = 'sine';
        oscillator.frequency.setValueAtTime(frequency, now);
        gain.gain.setValueAtTime(0.0001, now);
        gain.gain.exponentialRampToValueAtTime(volume, now + 0.015);
        gain.gain.exponentialRampToValueAtTime(0.0001, now + duration);
        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(now);
        oscillator.stop(now + duration + 0.02);
    }

    const playStartSound = () => playTone(620, 0.1, 0.07);
    const playPauseSound = () => playTone(420, 0.12, 0.06);
    const playResetSound = () => playTone(300, 0.12, 0.06);
    const playPhaseSound = () => {
        playTone(760, 0.12, 0.08);
        setTimeout(() => playTone(980, 0.16, 0.08), 140);
    };

    tabs.forEach(tab => tab.addEventListener('click', () => {
        showLoading('Cambiando de dia', 'Estamos organizando las actividades.');
        activateDay(tab.dataset.dayTab);
        setTimeout(hideLoading, 180);
    }));

    if (tabs.length) activateDay(tabs.some(tab => tab.dataset.dayTab === savedDay) ? savedDay : '1');
    [contextDay, contextLevel, contextLocation].forEach(control => control && control.addEventListener('change', filterActivities));

    if (contextActivity && window.localStorage.getItem('coupleExperienceActivity')) {
        contextActivity.value = window.localStorage.getItem('coupleExperienceActivity');
        if (contextDay) contextDay.value = window.localStorage.getItem('coupleExperienceAiDay') || contextDay.value;
        if (contextLevel) contextLevel.value = window.localStorage.getItem('coupleExperienceAiLevel') || contextLevel.value;
        if (contextLocation) contextLocation.value = window.localStorage.getItem('coupleExperienceAiLocation') || contextLocation.value;
        window.localStorage.removeItem('coupleExperienceActivity');
        window.localStorage.removeItem('coupleExperienceAiDay');
        window.localStorage.removeItem('coupleExperienceAiLevel');
        window.localStorage.removeItem('coupleExperienceAiLocation');
        filterActivities();
    }

    document.querySelectorAll('[data-next-card]').forEach(button => {
        button.addEventListener('click', () => {
            const current = button.closest('[data-activity-card]');
            const cards = activityCards.filter(card => !card.classList.contains('hidden'));
            const next = cards[cards.indexOf(current) + 1] || cards[0];
            showLoading('Buscando reto', 'Estamos moviendonos al siguiente juego.');
            next.scrollIntoView({ behavior: 'smooth', block: 'center' });
            setTimeout(hideLoading, 350);
        });
    });

    document.querySelectorAll('[data-use-for-ai]').forEach(button => {
        button.addEventListener('click', () => {
            const card = button.closest('[data-activity-card]');
            if (!card || !contextActivity) return;
            contextActivity.value = card.dataset.key;
            if (contextDay) contextDay.value = card.dataset.day;
            if (contextLevel) contextLevel.value = card.dataset.level;
            if (contextLocation) contextLocation.value = card.dataset.location;
            window.localStorage.setItem('coupleExperienceActivity', card.dataset.key);
            window.localStorage.setItem('coupleExperienceAiDay', card.dataset.day);
            window.localStorage.setItem('coupleExperienceAiLevel', card.dataset.level);
            window.localStorage.setItem('coupleExperienceAiLocation', card.dataset.location);
            showGameOutput('Actividad seleccionada como contexto para IA. Ahora puedes pedir una guia personalizada.');
            showLoading('Preparando contexto', 'Estamos conectando esta actividad con el asistente.');
            if (!document.querySelector('form[action*="/ai/suggest"]')) {
                window.location.href = assistantUrl;
                return;
            }
            window.scrollTo({ top: 0, behavior: 'smooth' });
            filterActivities();
            setTimeout(hideLoading, 350);
        });
    });

    const roulette = document.querySelector('[data-roulette]');
    if (roulette && contextDay && contextLevel && contextLocation) {
        roulette.addEventListener('click', () => {
            contextDay.value = pickRandom(['1', '2', '3', '4']);
            contextLevel.value = pickRandom(['2', '3', '4', '5']);
            contextLocation.value = pickRandom(['hotel', 'playa', 'restaurante', 'caminata', 'transporte', 'noche', 'descanso']);
            showLoading('Girando ruleta', 'Estamos eligiendo una combinacion para ustedes.');
            activateDay(contextDay.value);
            showGameOutput(`Ruleta: Dia ${contextDay.value}, nivel ${contextLevel.value}, lugar ${contextLocation.options[contextLocation.selectedIndex].text}.`);
            setTimeout(hideLoading, 350);
        });
    }

    const dice = document.querySelector('[data-dice]');
    if (dice) {
        dice.addEventListener('click', () => {
            const intensities = ['suave', 'jugueton', 'coqueto', 'intenso privado', 'pausa consciente', 'cierre con cuidado'];
            const actions = ['hablar', 'mirar', 'tocar con permiso', 'guiar', 'preguntar limites', 'cerrar'];
            showLoading('Lanzando dados', 'Estamos combinando intensidad y accion.');
            showGameOutput(`Dados: intensidad ${pickRandom(intensities)} + accion ${pickRandom(actions)}.`);
            setTimeout(hideLoading, 350);
        });
    }

    const timerStart = document.querySelector('[data-timer-start]');
    if (timerStart) {
        timerStart.addEventListener('click', () => {
            let seconds = 180;
            timerStart.disabled = true;
            const interval = setInterval(() => {
                const minutes = Math.floor(seconds / 60);
                const rest = String(seconds % 60).padStart(2, '0');
                showGameOutput(`Timer de conexion: ${minutes}:${rest}. Mantengan el foco en una sola actividad.`);
                seconds--;
                if (seconds < 0) {
                    clearInterval(interval);
                    timerStart.disabled = false;
                    showGameOutput('Timer terminado. Cierren diciendo: esto me acerco a ti porque...');
                }
            }, 1000);
        });
    }

    if (classicStart) classicStart.addEventListener('click', () => {
        if (classicInterval) return;
        playStartSound();
        if (classicState) classicState.textContent = 'Cronometro activo';
        classicInterval = setInterval(() => {
            classicSeconds++;
            updateClassic();
        }, 1000);
    });
    if (classicPause) classicPause.addEventListener('click', () => {
        if (!classicInterval) return;
        playPauseSound();
        clearInterval(classicInterval);
        classicInterval = null;
        if (classicState) classicState.textContent = 'Pausado';
    });
    if (classicReset) classicReset.addEventListener('click', () => {
        playResetSound();
        if (classicInterval) clearInterval(classicInterval);
        classicInterval = null;
        classicSeconds = 0;
        if (classicState) classicState.textContent = 'Listo para iniciar';
        updateClassic();
    });

    if (expressStart) expressStart.addEventListener('click', () => {
        if (expressInterval) return;
        playStartSound();
        expressInterval = setInterval(() => {
            expressSeconds--;
            if (expressSeconds <= 0) {
                playPhaseSound();
                if (expressIsChange) {
                    expressIsChange = false;
                    expressRoundCount++;
                    expressSeconds = 45;
                } else {
                    expressIsChange = true;
                    expressSeconds = 10;
                }
            }
            updateExpress();
        }, 1000);
    });
    if (expressPause) expressPause.addEventListener('click', () => {
        if (!expressInterval) return;
        playPauseSound();
        clearInterval(expressInterval);
        expressInterval = null;
    });
    if (expressReset) expressReset.addEventListener('click', () => {
        playResetSound();
        if (expressInterval) clearInterval(expressInterval);
        expressInterval = null;
        expressSeconds = 45;
        expressIsChange = false;
        expressRoundCount = 1;
        updateExpress();
    });
    if (soundToggle) soundToggle.addEventListener('click', () => {
        soundEnabled = !soundEnabled;
        window.localStorage.setItem('coupleTimerSound', soundEnabled ? 'on' : 'off');
        updateSoundToggle();
        if (soundEnabled) playStartSound();
    });

    if (levelSixOptionsScript) {
        try {
            levelSixOptions = JSON.parse(levelSixOptionsScript.textContent || '{}');
        } catch (error) {
            levelSixOptions = {};
        }

        resetLevelSixRoulette();

        document.querySelectorAll('[data-level-six-spin]').forEach(button => {
            button.addEventListener('click', spinLevelSixRoulette);
        });

        if (levelSixReset) {
            levelSixReset.addEventListener('click', () => {
                resetLevelSixRoulette();
                playResetSound();
            });
        }

        if (levelSixToggle) {
            levelSixToggle.addEventListener('click', () => {
                levelSixDetailVisible = !levelSixDetailVisible;
                if (levelSixDetailWrap) levelSixDetailWrap.classList.toggle('hidden', !levelSixDetailVisible);
                levelSixToggle.textContent = levelSixDetailVisible ? 'Ocultar actividad' : 'Mostrar actividad';
            });
        }

        if (levelSixTimerInput) {
            levelSixTimerInput.addEventListener('change', () => setLevelSixTimer(levelSixTimerInput.value));
        }

        if (levelSixTimerStart) {
            levelSixTimerStart.addEventListener('click', () => {
                if (levelSixTimerInterval) return;
                playStartSound();
                if (levelSixTimerState) levelSixTimerState.textContent = 'Activo';
                levelSixTimerInterval = setInterval(() => {
                    levelSixTimerSeconds--;
                    updateLevelSixTimerDisplay();

                    if (levelSixTimerSeconds <= 0) {
                        clearInterval(levelSixTimerInterval);
                        levelSixTimerInterval = null;
                        if (levelSixTimerState) levelSixTimerState.textContent = 'Terminado';
                        playPhaseSound();
                    }
                }, 1000);
            });
        }

        if (levelSixTimerPause) {
            levelSixTimerPause.addEventListener('click', () => {
                if (!levelSixTimerInterval) return;
                clearInterval(levelSixTimerInterval);
                levelSixTimerInterval = null;
                if (levelSixTimerState) levelSixTimerState.textContent = 'Pausado';
                playPauseSound();
            });
        }

        if (levelSixTimerReset) {
            levelSixTimerReset.addEventListener('click', () => {
                setLevelSixTimer(levelSixTimerInput ? levelSixTimerInput.value : 60);
                playResetSound();
            });
        }
    }

    updateClassic();
    updateExpress();
    updateSoundToggle();
</script>
