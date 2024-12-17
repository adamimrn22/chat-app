<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pesbuk 2.0</title>
    <link rel="icon" type="image/x-icon" href="{{ asset('icon.png') }}">
    @vite('resources/css/app.css')
</head>

<body class="bg-gray-100 h-screen flex flex-col">
    <div class="max-w-2xl mx-auto w-full flex-1 flex flex-col p-4">
        <div id="chat-container" class="flex-1 bg-white rounded-lg shadow-md p-4 overflow-y-auto mb-4 space-y-2">
            @foreach ($messages->reverse() as $message)
                <div class="flex {{ $message->user_id == auth()->id() ? 'justify-end' : 'justify-start' }}">
                    <div
                        class="min-w-[10%] p-2 rounded-lg {{ $message->user_id == auth()->id() ? 'bg-blue-500 text-white' : 'bg-gray-200 text-black' }}">
                        <strong
                            class="block text-sm mb-1 {{ $message->user_id == auth()->id() ? 'text-blue-100' : 'text-blue-600' }}">
                            {{ $message->user->name }}
                        </strong>
                        <span>{{ $message->message }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <form id="chat-form" class="flex space-x-2">
            @csrf
            <input type="text" id="message-input"
                class="flex-1 px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                placeholder="Type a message..." required>
            <button type="submit"
                class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 transition-colors">
                Send
            </button>
        </form>
    </div>

    @vite('resources/js/app.js')
    <script>
        const userId = @json(auth()->user()->id);
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const chatContainer = document.getElementById('chat-container');
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');

            // These should be passed from your Blade template or set dynamically
            const userId = @json(auth()->user()->id);
            const channelId = @json($channel->id); // Assuming you pass the channel ID

            // Form submission
            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = messageInput.value.trim();

                if (message) {
                    fetch('/messages', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content')
                            },
                            body: JSON.stringify({
                                message: message,
                                channel_id: channelId // Important: send the channel ID
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            messageInput.value = '';
                        })
                        .catch(error => console.error('Error:', error));
                }
            });


            // Echo setup for real-time messages
            Echo.private(`channel.${channelId}`)
                .listen('MessageSent', (e) => {
                    console.log('Message received:', e);
                    renderMessages([e.message]);
                });

            function renderMessages(messages) {
                const chatContainer = document.getElementById('chat-container');

                messages.forEach((message) => {
                    const messageDiv = document.createElement('div');
                    messageDiv.classList.add('flex');

                    // Align messages based on sender
                    if (message.user_id == userId) {
                        messageDiv.classList.add('justify-end');
                    } else {
                        messageDiv.classList.add('justify-start');
                    }

                    const messageContent = document.createElement('div');
                    messageContent.classList.add('max-w-[70%]', 'p-2', 'rounded-lg');

                    // Different styling based on sender
                    if (message.user_id == userId) {
                        messageContent.classList.add('bg-blue-500', 'text-white');
                    } else {
                        messageContent.classList.add('bg-gray-200', 'text-black');
                    }

                    const senderName = document.createElement('strong');
                    senderName.classList.add('block', 'text-sm', 'mb-1');
                    if (message.user_id == userId) {
                        senderName.classList.add('text-blue-100');
                    } else {
                        senderName.classList.add('text-blue-600');
                    }
                    senderName.textContent = message.user.name;

                    const messageText = document.createElement('span');
                    messageText.textContent = message.message;

                    messageContent.appendChild(senderName);
                    messageContent.appendChild(messageText);
                    messageDiv.appendChild(messageContent);

                    chatContainer.appendChild(messageDiv);
                });

                // Scroll to the bottom of the chat container
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</body>

</html>
