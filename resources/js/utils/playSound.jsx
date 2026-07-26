// Preloaded audio cache — plays instantly on first and subsequent calls
const cache = {};

const playSound = (soundFile) => {
    if (!cache[soundFile]) {
        const audio = new Audio(soundFile);
        audio.preload = "auto";
        audio.load();
        cache[soundFile] = audio;
    }
    const audio = cache[soundFile].cloneNode();
    audio.currentTime = 0;
    audio.play().catch(() => {});
};

export default playSound;
