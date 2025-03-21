<div class="w-[350px] bg-white border border-gray-300 rounded-lg shadow-lg p-5 z-50">
    <div class="widget flex flex-col gap-4 h-[400px]">
        <button class="close-button absolute top-3 right-3 bg-transparent border-none text-lg text-gray-600 hover:text-blue-500 transition-colors">×</button>
        <h2 class="text-xl font-semibold text-gray-800">AI Chat Bot</h2>
        <div class="chat-window flex-1 overflow-y-auto p-3 border border-gray-300 rounded-lg bg-gray-50">
            <div class="message bot max-w-[80%] p-3 rounded-lg bg-blue-50 text-gray-800 self-start mb-3">
                <div>Üdvözöllek! Hogyan segíthetek?</div>
                <div class="timestamp text-xs text-gray-600 mt-1">10:00</div>
            </div>
            <div class="message user max-w-[80%] p-3 rounded-lg bg-blue-500 text-white self-end mb-3">
                <div>Szia! Van egy kérdésem.</div>
                <div class="timestamp text-xs text-gray-200 mt-1">10:01</div>
            </div>
        </div>
        <form id="continue-chat-form" class="flex flex-col gap-3">
            <textarea id="new-question" name="question" placeholder="Új kérdés" class="w-full p-3 border border-gray-300 rounded-lg focus:border-blue-500 focus:outline-none" required></textarea>
            <button id="send_continue" type="submit" class="w-full p-3 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors">Küldés</button>
        </form>
    </div>
</div>
