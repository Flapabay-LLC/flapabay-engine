import Echo from 'laravel-echo';

window.Echo = new Echo({
    broadcaster: 'reverb',
    host: import.meta.env.VITE_REVERB_HOST + ':' + import.meta.env.VITE_REVERB_PORT,
});

// Minimal listener for test-reverb channel
if (window.Echo && typeof window.Echo.channel === 'function') {
    window.Echo.channel('test-reverb')
        .listen('TestReverbEvent', (e) => {
            console.log('Received event:', e);
        });
}
