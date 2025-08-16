(function() {
    const defaultConfig = {
        apiUrl: 'https://szakdolgozat.test/api',
        containerId: 'conversiveai-widget-container',
        cssUrl: 'https://szakdolgozat.test/css/widget/default.css',
        siteId: null,
        widgetName: 'ConversiveAI',
        pollingInterval: 10000 // 10 másodperces polling alapértelmezett
    };

    window.widgetConfig = Object.assign({}, defaultConfig, window.widgetConfig || {});

    // Globális változók a pollinghoz
    let pollingInterval = null;
    let lastMessageId = null;
    let isWidgetOpen = false;

    // CSS fájl betöltése
    function loadCSS() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = widgetConfig.cssUrl;
        link.onerror = () => showError('Nem sikerült betölteni a stíluslapot. A widget nem fog megfelelően megjelenni.');
        document.head.appendChild(link);
    }

    // Hibaüzenet megjelenítése
    function showError(message) {
        const container = document.getElementById(widgetConfig.containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="conversiveai-widget conversiveai-error">
                <button class="conversiveai-close-button" id="conversiveai-close-widget">×</button>
                <h2>Hiba történt</h2>
                <div class="conversiveai-error-message">${message}</div>
                <button id="conversiveai-retry-button">Újrapróbálkozás</button>
            </div>
        `;

        document.getElementById('conversiveai-retry-button').addEventListener('click', initWidget);
        document.getElementById('conversiveai-close-widget').addEventListener('click', () => {
            container.style.display = 'none';
            isWidgetOpen = false;
        });

        container.style.display = 'block';
        isWidgetOpen = true;
    }

    // Widget inicializálása
    function initWidget() {
        const container = document.getElementById(widgetConfig.containerId);
        if (!container) {
            console.error('Widget konténer nem található!');
            return;
        }

        // Kék gomb létrehozása a jobb alsó sarokban
        const toggleButton = document.createElement('div');
        toggleButton.id = 'conversiveai-widget-toggle-button';
        toggleButton.innerHTML = '<img src="https://szakdolgozat.test/widget/default.svg">';
        document.body.appendChild(toggleButton);

        // Gomb eseménykezelője
        toggleButton.addEventListener('click', () => {
            if (!isWidgetOpen) {
                const chatId = getChatId();
                if (chatId) {
                    loadChat(chatId);
                } else {
                    renderStartChatForm();
                }
                container.style.display = 'block';
                isWidgetOpen = true;
            } else {
                container.style.display = 'none';
                isWidgetOpen = false;
            }
        });

        // Alapértelmezett állapot: elrejtve
        container.style.display = 'none';
        isWidgetOpen = false;

        // Chat ID betöltése a localStorage-ből
        const chatId = getChatId();
        if (chatId) {
            // Csak betöltjük a chatet ha a widget megnyílik
        } else {
            // Kezdeti űrlapot előkészítjük
        }
    }

    // Chat ID lekérése a localStorage-ből
    function getChatId() {
        return localStorage.getItem('chat_id');
    }

    // Chat ID mentése a localStorage-ba
    function saveChatId(chatId) {
        localStorage.setItem('chat_id', chatId);
    }

    // Chat ID törlése a localStorage-ból
    function clearChatId() {
        localStorage.removeItem('chat_id');
    }

    // API hívás kezelése
    async function fetchData(url, options = {}) {
        try {
            const response = await fetch(url, {
                ...options,
                headers: {
                    ...options.headers,
                },
            });

            if (!response.ok) {
                // Próbáljuk meg feldolgozni a JSON válasz hibaüzenetét
                let errorMessage = `Hiba a kérés során: ${response.statusText}`;
                try {
                    const errorData = await response.json();
                    if (errorData.message) {
                        errorMessage = errorData.message;
                    } else if (errorData.error) {
                        errorMessage = errorData.error;
                    }
                } catch (jsonError) {
                    // Ha nem JSON a válasz, használjuk az alapértelmezett hibaüzenetet
                }

                const error = new Error(errorMessage);
                error.status = response.status;
                throw error;
            }

            return await response.json();
        } catch (error) {
            console.error('Hiba történt:', error);
            throw error;
        }
    }

    // Beszélgetés betöltése
    async function loadChat(chatId, initialLoad = true) {
        try {
            if (initialLoad) {
                const container = document.getElementById(widgetConfig.containerId);
                container.innerHTML = `
                    <div class="conversiveai-widget">
                        <div id="conversiveai-loading-animation">Betöltés...</div>
                    </div>
                `;
            }

            const data = await fetchData(`${widgetConfig.apiUrl}/messages/${widgetConfig.siteId}?chat_id=${chatId}`);

            // Frissítjük az utolsó üzenet ID-ját
            if (data.messages && data.messages.length > 0) {
                const newLastMessageId = data.messages[data.messages.length - 1].id;
                if (newLastMessageId !== lastMessageId) {
                    renderChat(data);
                    lastMessageId = newLastMessageId;
                }
            } else {
                renderChat(data);
            }

            // Indítsuk a pollingot, ha még nem fut és a widget nyitva van
            if (!pollingInterval && chatId && isWidgetOpen) {
                startPolling(chatId);
            }
        } catch (error) {
            console.error('Hiba történt a beszélgetés betöltésekor:', error);

            // Ha 404-es hiba, akkor töröljük a chat_id-t és jelenítsük meg az új chat formot
            if (error.status === 404) {
                clearChatId();
                stopPolling();
                if (initialLoad) {
                    showChatNotFoundError(error.message);
                }
                return;
            }

            if (initialLoad) {
                showError(`Nem sikerült betölteni a beszélgetést: ${error.message}`);
            }
        }
    }

    // Chat nem található hiba megjelenítése
    function showChatNotFoundError(message) {
        const container = document.getElementById(widgetConfig.containerId);
        if (!container) return;

        container.innerHTML = `
            <div class="conversiveai-widget conversiveai-error">
                <button class="conversiveai-close-button" id="conversiveai-close-widget">×</button>
                <h2>Beszélgetés nem található</h2>
                <div class="conversiveai-error-message">${message}</div>
                <button id="conversiveai-start-new-chat">Új beszélgetés indítása</button>
            </div>
        `;

        document.getElementById('conversiveai-start-new-chat').addEventListener('click', () => {
            renderStartChatForm();
        });

        document.getElementById('conversiveai-close-widget').addEventListener('click', () => {
            container.style.display = 'none';
            isWidgetOpen = false;
        });

        container.style.display = 'block';
        isWidgetOpen = true;
    }

    // Polling indítása
    function startPolling(chatId) {
        // Leállítjuk az előző pollingt ha van
        if (pollingInterval) {
            clearInterval(pollingInterval);
        }

        // Azonnali ellenőrzés
        loadChat(chatId, false);

        // Új polling indítása
        pollingInterval = setInterval(() => {
            if (isWidgetOpen) {
                loadChat(chatId, false);
            }
        }, widgetConfig.pollingInterval);

        // Az oldal elhagyásakor leállítjuk a pollingot
        window.addEventListener('beforeunload', stopPolling);
    }

    // Polling leállítása
    function stopPolling() {
        if (pollingInterval) {
            clearInterval(pollingInterval);
            pollingInterval = null;
        }
        lastMessageId = null;
    }

    // Új beszélgetés indítása
    async function startChat(nickname, email, question) {
        try {
            const data = await fetchData(`${widgetConfig.apiUrl}/submit-message/${widgetConfig.siteId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ nickname, email, message: question }),
            });

            if (data && data.data && data.data.chat_id) {
                saveChatId(data.data.chat_id);
                loadChat(data.data.chat_id);
                startPolling(data.data.chat_id);
            } else {
                throw new Error('Nem sikerült létrehozni a beszélgetést.');
            }
        } catch (error) {
            showError(`Nem sikerült elindítani a beszélgetést: ${error.message}`);
            // Visszaállítjuk a formot
            const form = document.getElementById('conversiveai-start-chat-form');
            const loadingAnimation = document.getElementById('conversiveai-loading-animation');
            if (form) form.style.display = 'block';
            if (loadingAnimation) loadingAnimation.style.display = 'none';
        }
    }

    // Chat törlése
    async function closeChat(chatId) {
        try {
            const data = await fetchData(`${widgetConfig.apiUrl}/messages/close/${widgetConfig.siteId}`, {
                method: 'PATCH',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: chatId }),
            });

            if (data) {
                clearChatId();
                stopPolling();
                renderStartChatForm();
            }
        } catch (error) {
            showError(`Nem sikerült lezárni a beszélgetést: ${error.message}`);
        }
    }

    // Kérdés frissítése
    async function continueChat(chatId, question) {
        try {
            const data = await fetchData(`${widgetConfig.apiUrl}/submit-message/${widgetConfig.siteId}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ chat_id: chatId, message: question }),
            });

            if (data) {
                // Azonnal frissítjük a chatet
                await loadChat(chatId, false);
                // Újraindítjuk a pollingot ha leállt
                if (!pollingInterval) {
                    startPolling(chatId);
                }
            }
        } catch (error) {
            showError(`Nem sikerült elküldeni az üzenetet: ${error.message}`);
            // Visszaállítjuk a formot
            const form = document.getElementById('conversiveai-continue-chat-form');
            const loadingAnimation = document.getElementById('conversiveai-loading-animation');
            if (form) form.style.display = 'block';
            if (loadingAnimation) loadingAnimation.style.display = 'none';
        }
    }

    // Beszélgetés űrlap renderelése
    function renderStartChatForm() {
        const container = document.getElementById(widgetConfig.containerId);
        stopPolling();

        container.innerHTML = `
            <div class="conversiveai-widget">
                <button class="conversiveai-close-button" id="conversiveai-close-widget">×</button>
                <h2>${widgetConfig.widgetName}</h2>
                <h3>Új beszélgetés indítása</h3>
                <form id="conversiveai-start-chat-form">
                    <input type="text" id="conversiveai-nickname" name="nickname" placeholder="Becenév" required>
                    <input type="email" id="conversiveai-email" name="email" placeholder="Email cím" required>
                    <textarea id="conversiveai-question" name="question" placeholder="Kérdésed" required></textarea>
                    <button type="submit">Küldés</button>
                </form>
                <div id="conversiveai-loading-animation" style="display: none;">Betöltés...</div>
                <div id="conversiveai-error-message" class="conversiveai-error-message" style="display: none;"></div>
            </div>
        `;

        const form = document.getElementById('conversiveai-start-chat-form');
        const loadingAnimation = document.getElementById('conversiveai-loading-animation');
        const errorMessage = document.getElementById('conversiveai-error-message');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const nickname = document.getElementById('conversiveai-nickname').value;
            const email = document.getElementById('conversiveai-email').value;
            const question = document.getElementById('conversiveai-question').value;

            if (!nickname || !email || !question) {
                errorMessage.textContent = 'Minden mezőt ki kell tölteni!';
                errorMessage.style.display = 'block';
                return;
            }

            // Form elrejtése és animáció megjelenítése
            form.style.display = 'none';
            loadingAnimation.style.display = 'block';
            errorMessage.style.display = 'none';

            startChat(nickname, email, question);
        });

        const closeButton = document.getElementById('conversiveai-close-widget');
        closeButton.addEventListener('click', () => {
            const chatId = getChatId();
            if (!chatId) {
                container.style.display = 'none';
                isWidgetOpen = false;
            } else {
                showCloseOptions();
            }
        });
    }

    // Beszélgetés renderelése
    function renderChat(data) {
        const container = document.getElementById(widgetConfig.containerId);
        const wasScrolledToBottom = isScrolledToBottom('conversiveai-chat-window');

        container.innerHTML = `
            <div class="conversiveai-widget">
                <button class="conversiveai-close-button" id="conversiveai-close-widget">×</button>
                <h2>${widgetConfig.widgetName}</h2>
                <div class="conversiveai-chat-window" id="conversiveai-chat-window">
                    ${renderMessages(data.messages)}
                </div>
                <form id="conversiveai-continue-chat-form">
                    <textarea id="conversiveai-new-question" name="question" placeholder="Új kérdés" required></textarea>
                    <button id="conversiveai-send_continue" type="submit">Küldés</button>
                </form>
                <div id="conversiveai-loading-animation" style="display: none;">Betöltés...</div>
                <div id="conversiveai-error-message" class="conversiveai-error-message" style="display: none;"></div>
            </div>
        `;

        const form = document.getElementById('conversiveai-continue-chat-form');
        const loadingAnimation = document.getElementById('conversiveai-loading-animation');
        const errorMessage = document.getElementById('conversiveai-error-message');
        const textarea = document.getElementById('conversiveai-new-question');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const question = textarea.value.trim();

            if (question === '') {
                errorMessage.textContent = 'Kérjük, írjon be egy üzenetet!';
                errorMessage.style.display = 'block';
                return;
            }

            // Form elrejtése és animáció megjelenítése
            form.style.display = 'none';
            loadingAnimation.style.display = 'block';
            errorMessage.style.display = 'none';

            continueChat(data.chat_id, question);
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        const closeButton = document.getElementById('conversiveai-close-widget');
        closeButton.addEventListener('click', () => {
            showCloseOptions();
        });

        const chatWindow = document.getElementById('conversiveai-chat-window');
        if (wasScrolledToBottom || chatWindow.scrollHeight - chatWindow.clientHeight < 50) {
            chatWindow.scrollTop = chatWindow.scrollHeight;
        }
    }

    // Üzenetek renderelése
    function renderMessages(messages) {
        return messages.map(message => `
            <div class="conversiveai-message ${message.sender_role === 'user' ? 'user' : 'bot'}">
                <div>${message.message}</div>
                <div class="conversiveai-timestamp">
                    ${new Date(message.created_at).toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'})}
                </div>
            </div>
        `).join('');
    }

    // Segédfüggvény: a chat ablak alján vagyunk-e
    function isScrolledToBottom(elementId) {
        const element = document.getElementById(elementId);
        if (!element) return false;
        return Math.abs(element.scrollHeight - element.scrollTop - element.clientHeight) < 10;
    }

    // Bezárási lehetőségek megjelenítése
    function showCloseOptions() {
        const container = document.getElementById(widgetConfig.containerId);
        const chatId = getChatId();

        if (!chatId) {
            container.style.display = 'none';
            isWidgetOpen = false;
            return;
        }

        container.innerHTML = `
            <div class="conversiveai-widget">
                <h2>Bezárási lehetőségek</h2>
                <button id="conversiveai-return-back">Vissza a beszélgetéshez</button>
                <button id="conversiveai-close-temporarily">Beszélgetés elrejtése</button>
                <button id="conversiveai-close-permanently">Végleges bezárás</button>
                <div id="conversiveai-error-message" class="conversiveai-error-message" style="display: none;"></div>
            </div>
        `;

        const returnBackButton = document.getElementById('conversiveai-return-back');
        const closeTemporarilyButton = document.getElementById('conversiveai-close-temporarily');
        const closePermanentlyButton = document.getElementById('conversiveai-close-permanently');
        const errorMessage = document.getElementById('conversiveai-error-message');

        returnBackButton.addEventListener('click', () => {
            const chatId = getChatId();
            if (chatId) {
                loadChat(chatId);
            } else {
                showError('Nem található aktív beszélgetés.');
            }
        });

        closeTemporarilyButton.addEventListener('click', () => {
            container.style.display = 'none';
            isWidgetOpen = false;
        });

        closePermanentlyButton.addEventListener('click', async () => {
            try {
                closeTemporarilyButton.disabled = true;
                closePermanentlyButton.disabled = true;
                await closeChat(chatId);
                container.style.display = 'none';
                isWidgetOpen = false;
            } catch (error) {
                errorMessage.textContent = 'Hiba történt a beszélgetés lezárása közben.';
                errorMessage.style.display = 'block';
                closeTemporarilyButton.disabled = false;
                closePermanentlyButton.disabled = false;
            }
        });
    }

    // Inicializálás
    loadCSS();
    initWidget();
})();
