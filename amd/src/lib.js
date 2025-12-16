define(['jquery', 'core/str'], ($, Str) => {

    /* global assistantName, userName */

    /* eslint-disable no-unused-vars */
    let questionString = 'Ask a question...';
    let errorString = 'An error occurred! Please try again later.';
    /* eslint-enable no-unused-vars */

    let chatData = {};

    const init = (data) => {

        const { modId, api_type, persistConvo, userId } = data;

        // Load strings
        Str.get_strings([
            {key: 'askaquestion', component: 'mod_openaichat'},
            {key: 'erroroccurred', component: 'mod_openaichat'},
        ]).then(([q, e]) => {
            questionString = q;
            errorString = e;
        });

        if (api_type === 'assistant') {
            initChatStorage(modId, persistConvo);
        }

        if (checkUserLimit(modId, userId) === false) {
            disableButton();
            return;
        }

        updateRemainingQuestions(modId, userId);

        bindEvents(modId, api_type, userId);
    };

    /* ---------- helpers ---------- */

    const initChatStorage = (modId, persistConvo) => {
        chatData = JSON.parse(localStorage.getItem('mod_openaichat_data')) || {};

        chatData[modId] = chatData[modId] || {};

        if (chatData[modId].threadId && persistConvo === '1') {
            fetch(`${M.cfg.wwwroot}/mod/openaichat/api/thread.php?modId=${modId}&thread_id=${chatData[modId].threadId}`)
                .then(r => r.json())
                .then(messages => {
                    messages.forEach(m =>
                        addToChatLog(m.role === 'user' ? 'user' : 'bot', m.message)
                    );
                })
                .catch(() => {
                    chatData[modId] = {};
                    persistChat();
                });
        }

        persistChat();
    };

    const persistChat = () => {
        localStorage.setItem('mod_openaichat_data', JSON.stringify(chatData));
    };

    const bindEvents = (modId, api_type, userId) => {

        const input = document.querySelector('#openai_input');

        input.addEventListener('keyup', e => {
            if (e.key === 'Enter' && input.value) {
                handleSubmit(input.value, modId, api_type, userId);
                input.value = '';
            }
        });

        document.querySelector('.mod_openaichat #go')
            .addEventListener('click', () => {
                if (input.value) {
                    handleSubmit(input.value, modId, api_type, userId);
                    input.value = '';
                }
            });

        document.querySelector('.mod_openaichat #refresh')
            .addEventListener('click', () => clearHistory(modId));
    };

    const handleSubmit = (message, modId, api_type, userId) => {
        if (checkUserLimit(modId, userId) === false) {
            disableButton();
            return;
        }

        addToChatLog('user', message);
        createCompletion(message, modId, api_type, userId);
    };

    const updateRemainingQuestions = (modId, userId) => {
        const remaining = checkUserLimit(modId, userId);
        if (remaining !== -1) {
            document.querySelector('#remaining-questions').innerText =
                `You have ${remaining} question(s) remaining.`;
        }
    };

    const addToChatLog = (type, message) => {
        const container = document.querySelector('#openai_chat_log');

        const el = document.createElement('div');
        el.classList.add('openai_message', ...type.split(' '));

        const span = document.createElement('span');
        span.innerHTML = message;

        el.append(span);
        container.append(el);

        if (span.offsetWidth) {
            el.style.width = `${span.offsetWidth + 40}px`;
        }

        container.scrollTop = container.scrollHeight;
    };

    const clearHistory = (modId) => {
        chatData[modId] = {};
        persistChat();
        document.querySelector('#openai_chat_log').innerHTML = '';
    };

    const createCompletion = (message, modId, api_type, userId) => {

        let threadId = null;

        if (api_type === 'assistant') {
            threadId = chatData?.[modId]?.threadId || null;
        }

        const history = buildTranscript();

        toggleControls(true);
        addToChatLog('bot loading', '...');

        let sesskey = M.cfg.sesskey;

        fetch(`${M.cfg.wwwroot}/mod/openaichat/api/completion.php`, {
            method: 'POST',
            body: JSON.stringify({sesskey, message, history, modId, threadId })
        })
        .then(r => {
            removeLastMessage();
            toggleControls(false);
            if (!r.ok) {
                throw new Error(r.statusText);
            }
            return r.json();
        })
        .then(data => {
            storeUserLog(modId, message, data.message);
            addToChatLog('bot', data.message);

            if (data.thread_id) {
                chatData[modId].threadId = data.thread_id;
                persistChat();
            }

            updateRemainingQuestions(modId, userId);
            document.querySelector('#openai_input').focus();
        })
        .catch(() => {
            const input = document.querySelector('#openai_input');
            input.classList.add('error');
            input.placeholder = errorString;
        });
    };

    const buildTranscript = () => {
        const transcript = [];
        document.querySelectorAll('.openai_message').forEach((msg, i, all) => {
            if (i === all.length - 1) {
                return;
            }
            transcript.push({ user: msg.classList.contains('bot') ? assistantName : userName, message: msg.innerText });
        });
        return transcript;
    };

    const storeUserLog = (modId, requestMessage, responseMessage) => {
        $.post(`${M.cfg.wwwroot}/mod/openaichat/api/record_log.php`, {
            modId, requestMessage, responseMessage
        });
    };

    const checkUserLimit = (modId, userId) => {
        return $.ajax({
            method: 'POST',
            url: `${M.cfg.wwwroot}/mod/openaichat/api/question_counter.php`,
            data: { modId, userId },
            dataType: 'json',
            async: false
        }).responseJSON;
    };

    const toggleControls = (disabled) => {
        document.querySelector('.mod_openaichat #control_bar')
            .classList.toggle('disabled', disabled);
    };

    const removeLastMessage = () => {
        const c = document.querySelector('#openai_chat_log');
        if (c.lastElementChild) {
            c.removeChild(c.lastElementChild);
        }
    };

    const disableButton = () => {
        const input = document.querySelector('#openai_input');
        input.classList.add('error');
        input.placeholder = 'Limit reached';
        document.querySelector('.mod_openaichat #control_bar').classList.add('disabled');
        document.querySelector('#remaining-questions').style.display = 'none';
        return false;
    };

    return { init };
});
