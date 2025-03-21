(function() {
    // Widget konfiguráció inicializálása alapértékekkel
    const defaultConfig = {
        apiUrl: 'https://szakdolgozat.test/api',
        containerId: 'conversiveai-widget-container',
        cssUrl: 'https://szakdolgozat.test/css/widget/default.css',
        siteId: null,
    };

    window.widgetConfig = Object.assign({}, defaultConfig, window.widgetConfig || {});

    // CSS fájl betöltése
    function loadCSS() {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = widgetConfig.cssUrl;
        document.head.appendChild(link);
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
        toggleButton.id = 'widget-toggle-button';
        toggleButton.innerHTML = '<img src="https://szakdolgozat.test/widget/icon1.png">';
        document.body.appendChild(toggleButton);

        // Gomb eseménykezelője
        toggleButton.addEventListener('click', () => {
            if (container.style.display === 'none') {
                const chatId = getChatId();
                if (chatId) {
                    loadChat(chatId); // Ha van chat_id, betöltjük a chatet
                } else {
                    renderStartChatForm(); // Ha nincs chat_id, az űrlapot jelenítjük meg
                }
                container.style.display = 'block';
            } else {
                container.style.display = 'none';
            }
        });

        // Alapértelmezett állapot: elrejtve
        container.style.display = 'none';

        // Chat ID betöltése a localStorage-ből
        const chatId = getChatId();
        if (chatId) {
            loadChat(chatId);
        } else {
            renderStartChatForm();
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
                throw new Error(`Hiba a kérés során: ${response.statusText}`);
            }
            const responseData = await response.json();
            return responseData;
        } catch (error) {
            console.error('Hiba történt:', error);
            return null;
        }
    }

    // Beszélgetés betöltése
    async function loadChat(chatId) {
        const data = await fetchData(`${widgetConfig.apiUrl}/messages/${widgetConfig.siteId}?chat_id=${chatId}`);
        if (data) {
            renderChat(data);
        }
    }

    // Új beszélgetés indítása
    async function startChat(nickname, email, question) {
        const data = await fetchData(`${widgetConfig.apiUrl}/submit-message/${widgetConfig.siteId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ nickname, email, message: question }),
        });

        if (data && data.data && data.data.chat_id) {
            saveChatId(data.data.chat_id);
            loadChat(data.data.chat_id);
        } else {
            console.error('Hiba: Nem sikerült létrehozni a beszélgetést.', data);
        }
    }

    // Chat törlése
    async function deleteChat(chatId) {
        const data = await fetchData(`${widgetConfig.apiUrl}/messages/delete/${widgetConfig.siteId}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId }),
        });

        if (data) {
            clearChatId();
            renderStartChatForm();
        }
    }

    // Kérdés frissítése
    async function continueChat(chatId, question) {
        const data = await fetchData(`${widgetConfig.apiUrl}/submit-message/${widgetConfig.siteId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ chat_id: chatId, message: question }),
        });

        if (data) {
            loadChat(chatId);
        }
    }

    // Beszélgetés űrlap renderelése
    function renderStartChatForm() {
        const container = document.getElementById(widgetConfig.containerId);
        container.innerHTML = `
            <div class="widget">
                <button class="close-button" id="close-widget">×</button>
                <h2>Új beszélgetés indítása</h2>
                <form id="start-chat-form">
                    <input type="text" id="nickname" name="nickname" placeholder="Becenév" required>
                    <input type="email" id="email" name="email" placeholder="Email cím" required>
                    <textarea id="question" name="question" placeholder="Kérdésed" required></textarea>
                    <button type="submit">Küldés</button>
                </form>
                <div id="loading-animation" style="display: none;">Betöltés...</div>
            </div>
        `;

        const form = document.getElementById('start-chat-form');
        const loadingAnimation = document.getElementById('loading-animation');

        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const nickname = document.getElementById('nickname').value;
            const email = document.getElementById('email').value;
            const question = document.getElementById('question').value;

            if (!nickname || !email || !question) {
                console.error('Minden mezőt ki kell tölteni!');
                return;
            }

            // Form elrejtése és animáció megjelenítése
            form.style.display = 'none';
            loadingAnimation.style.display = 'block';

            startChat(nickname, email, question).then(() => {
                // Válasz érkezése után animáció elrejtése és form visszaállítása
                form.style.display = 'block';
                loadingAnimation.style.display = 'none';
            });
        });

        const closeButton = document.getElementById('close-widget');
        closeButton.addEventListener('click', () => {
            const chatId = getChatId();
            if (!chatId) {
                container.style.display = 'none'; // Ha nincs chat_id, csak elrejtjük
            } else {
                showCloseOptions(); // Ha van chat_id, bezárási lehetőségeket mutatunk
            }
        });
    }

    // Beszélgetés renderelése
    function renderChat(data) {
        const container = document.getElementById(widgetConfig.containerId);
        container.innerHTML = `
            <div class="widget">
                <button class="close-button" id="close-widget">×</button>
                <h2>AI Chat Bot</h2>
                <div class="chat-window" id="chat-window">
                    ${renderMessages(data.messages)}
                </div>
                <form id="continue-chat-form">
                    <textarea id="new-question" name="question" placeholder="Új kérdés" required></textarea>
                    <button id="send_continue" type="submit">Küldés</button>
                </form>
                <div id="loading-animation" style="display: none;">Betöltés...</div>
            </div>
        `;

        const form = document.getElementById('continue-chat-form');
        const loadingAnimation = document.getElementById('loading-animation');
        const textarea = document.getElementById('new-question');

        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const question = textarea.value;
            if (question.trim() === '') return;

            // Form elrejtése és animáció megjelenítése
            form.style.display = 'none';
            loadingAnimation.style.display = 'block';

            continueChat(data.chat_id, question).then(() => {
                // Válasz érkezése után animáció elrejtése és form visszaállítása
                form.style.display = 'block';
                loadingAnimation.style.display = 'none';
            });
        });

        textarea.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                form.dispatchEvent(new Event('submit'));
            }
        });

        const closeButton = document.getElementById('close-widget');
        closeButton.addEventListener('click', () => {
            showCloseOptions();
        });

        const chatWindow = document.getElementById('chat-window');
        chatWindow.scrollTop = chatWindow.scrollHeight;
    }

    // Üzenetek renderelése
    function renderMessages(messages) {
        return messages.map(message => `
            <div class="message ${message.sender_role === 'bot' ? 'bot' : 'user'}">
                <div>${message.message}</div>
                <div class="timestamp">${new Date(message.created_at).toLocaleTimeString()}</div>
            </div>
        `).join('');
    }

    // Bezárási lehetőségek megjelenítése
    function showCloseOptions() {
        const container = document.getElementById(widgetConfig.containerId);
        const chatId = getChatId();

        // Ha nincs chat_id, csak elrejtjük az ablakot
        if (!chatId) {
            container.style.display = 'none';
            return;
        }

        // Ha van chat_id, megjelenítjük a bezárási lehetőségeket
        container.innerHTML = `
            <div class="widget">
                <h2>Bezárási lehetőségek</h2>
                <button id="close-temporarily">Csak elrejtés</button>
                <button id="close-permanently">Végleges bezárás</button>
            </div>
        `;

        const closeTemporarilyButton = document.getElementById('close-temporarily');
        const closePermanentlyButton = document.getElementById('close-permanently');

        // Csak elrejtés gomb
        closeTemporarilyButton.addEventListener('click', () => {
            container.style.display = 'none';
        });

        // Végleges bezárás gomb
        closePermanentlyButton.addEventListener('click', () => {
            deleteChat(chatId);
            container.style.display = 'none';
        });
    }

    // CSS betöltése és widget inicializálása
    loadCSS();
    initWidget();
})();
