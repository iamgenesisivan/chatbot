<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const messages = ref([]);
const newMessage = ref("");
const loading = ref(false);

const fetchHistory = async () => {
    try {
        const res = await axios.post('/ask-ollama', { prompt: "Hello" });
        messages.value = res.data.history;
    } catch (error) {
        console.error("Failed to load chat history", error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) return;

    messages.value.push({ role: "user", content: newMessage.value });
    const userInput = newMessage.value;
    newMessage.value = "";
    loading.value = true;

    try {
        const response = await axios.post('/ask-ollama', { prompt: userInput });
        messages.value.push({ role: "assistant", content: response.data.response });
    } catch (error) {
        messages.value.push({ role: "assistant", content: "Error: Unable to connect to AI." });
    } finally {
        loading.value = false;
    }
};

const clearChat = async () => {
    await axios.post('/clear-chat');
    messages.value = [];
};

onMounted(fetchHistory);
</script>

<template>
    <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg p-6">
        <h2 class="text-2xl font-semibold mb-4 text-center">💬 AI Chat</h2>

        <!-- Chat Window - Increased Height -->
        <div class="h-[500px] overflow-y-auto border p-4 rounded bg-gray-100">
            <div v-for="(msg, index) in messages" :key="index"
                class="flex my-2"
                :class="msg.role === 'user' ? 'justify-end' : 'justify-start'">
                <p :class="msg.role === 'user' ? 'bg-blue-500 text-white' : 'bg-gray-300 text-black'"
                   class="inline-block px-4 py-2 rounded-lg max-w-xs text-sm md:text-base">
                    {{ msg.content }}
                </p>
            </div>
        </div>

        <!-- Input Field -->
        <div class="mt-4 flex">
            <input v-model="newMessage" @keyup.enter="sendMessage"
                   class="flex-1 p-3 border rounded-lg text-lg"
                   placeholder="Type a message..." />
            <button @click="sendMessage"
                    class="ml-3 bg-blue-600 hover:bg-blue-700 text-white px-5 py-3 rounded-lg">
                Send
            </button>
        </div>

        <!-- Clear Chat Button -->
        <button @click="clearChat"
                class="mt-4 bg-red-500 hover:bg-red-600 text-white px-5 py-3 rounded-lg w-full">
            Clear Chat
        </button>
    </div>
</template>
