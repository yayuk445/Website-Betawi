 const API_BASE_URL = './betawi-ai/api'; // 🔑 arahkan ke folder betawi-ai/api
        let sessionId = generateSessionId();

        function generateSessionId() {
            return 'session_' + Math.random().toString(36).substr(2, 9) + '_' + Date.now();
        }
        function updateStatus(status, message) {
            const indicator = document.getElementById('statusIndicator');
            indicator.className = `status-indicator status-${status}`;
            const icons = { online: '🟢', offline: '🔴', connecting: '🟡' };
            indicator.innerHTML = `${icons[status]} ${message}`;
        }
        function addMessage(content, isUser = false, timestamp = null) {
            const messagesContainer = document.getElementById('chatMessages');
            const welcomeMessage = messagesContainer.querySelector('.welcome-message');
            if (welcomeMessage && !isUser) welcomeMessage.remove();
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${isUser ? 'user' : 'assistant'}`;
            const timeStr = timestamp ? new Date(timestamp).toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'}) : new Date().toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'});
            messageDiv.innerHTML = `<div class="message-avatar">${isUser ? 'U' : '🏛️'}</div><div class="message-content">${content}<div class="message-time">${timeStr}</div></div>`;
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }
        function showTypingIndicator() { document.getElementById('typingIndicator').style.display = 'flex'; document.getElementById('chatMessages').scrollTop = document.getElementById('chatMessages').scrollHeight; }
        function hideTypingIndicator() { document.getElementById('typingIndicator').style.display = 'none'; }
        async function sendMessage() {
            const input = document.getElementById('chatInput');
            const sendButton = document.getElementById('sendButton');
            const message = input.value.trim();
            if (!message) return;
            addMessage(message, true);
            input.value = ''; sendButton.disabled = true; showTypingIndicator(); updateStatus('connecting', 'Mengirim pesan...');
            try {
                const response = await fetch(`${API_BASE_URL}/chat.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message: message, sessionId: sessionId}) });
                const data = await response.json();
                if (data.status === 'success') { addMessage(data.message, false, data.timestamp); updateStatus('online', 'Backend Siap'); }
                else throw new Error(data.message || 'Unknown error');
            } catch (error) {
                console.error('Error:', error); let errorMsg = 'Maaf nih, lagi ada gangguan teknis. ';
                if (error.message.includes('Failed to fetch')) { errorMsg += 'Cek koneksi backend atau server ya!'; updateStatus('offline', 'Backend Error'); }
                else { errorMsg += error.message; updateStatus('offline', 'API Error'); }
                addMessage(errorMsg);
            } finally { hideTypingIndicator(); sendButton.disabled = false; input.focus(); }
        }
        function sendSuggestion(suggestion) { document.getElementById('chatInput').value = suggestion; sendMessage(); }
        async function clearChat() {
            if (!confirm('Yakin mau hapus semua chat?')) return;
            try {
                const response = await fetch(`${API_BASE_URL}/clear.php`, { method:'POST', headers:{'Content-Type':'application/x-www-form-urlencoded'}, body:`session_id=${sessionId}` });
                const data = await response.json();
                if (data.status === 'success') {
                    document.getElementById('chatMessages').innerHTML = `<div class="welcome-message"><h3>Chat berhasil dihapus! 🎭</h3><p>Silakan mulai percakapan baru tentang budaya Betawi.</p><div class="suggestions"><button class="suggestion-chip" onclick="sendSuggestion('Ceritakan tentang sejarah Betawi')">📚 Sejarah Betawi</button><button class="suggestion-chip" onclick="sendSuggestion('Apa saja makanan khas Betawi?')">🍜 Kuliner Betawi</button><button class="suggestion-chip" onclick="sendSuggestion('Bagaimana cara belajar bahasa Betawi?')">🗣️ Bahasa Betawi</button><button class="suggestion-chip" onclick="sendSuggestion('Jelaskan tentang seni dan budaya Betawi')">🎨 Seni Betawi</button></div></div>`;
                } else throw new Error(data.message);
            } catch (error) { console.error('Error clearing chat:', error); alert('Gagal menghapus chat: ' + error.message); }
        }
        async function loadHistory() {
            try {
                updateStatus('connecting', 'Memuat history...');
                const response = await fetch(`${API_BASE_URL}/history.php?session_id=${sessionId}&limit=20`);
                const data = await response.json();
                const messagesContainer = document.getElementById('chatMessages'); messagesContainer.innerHTML = '';
                if (data.status === 'success') {
                    if (data.history.length === 0) messagesContainer.innerHTML = `<div class="welcome-message"><h3>Belum ada history chat 📜</h3><p>Mulai percakapan untuk melihat history.</p></div>`;
                    else data.history.forEach(msg => { addMessage(msg.message, msg.role === 'user', msg.created_at); });
                    updateStatus('online', 'History dimuat');
                } else throw new Error(data.message);
            } catch (error) { console.error('Error loading history:', error); updateStatus('offline', 'Error memuat history'); alert('Gagal memuat history: ' + error.message); }
        }
        async function testBackendConnection() {
            try {
                updateStatus('connecting', 'Mengecek backend...');
                const response = await fetch(`${API_BASE_URL}/chat.php`, { method:'POST', headers:{'Content-Type':'application/json'}, body: JSON.stringify({message: 'test connection', sessionId: sessionId}) });
                if (response.ok) updateStatus('online', 'Backend Siap');
                else throw new Error('Backend not responding');
            } catch (error) { updateStatus('offline', 'Backend Error'); console.error('Backend connection failed:', error); }
        }
        document.getElementById('chatInput').addEventListener('keypress', function(e) { if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendMessage(); } });
        document.getElementById('chatInput').focus();
        window.addEventListener('load', () => { testBackendConnection(); });