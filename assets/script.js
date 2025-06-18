function copyCodeBlock(button) {
    const pre = button.closest('pre');
    if (!pre) return;

    const code = pre.querySelector('code');
    if (!code) return;

    const text = code.innerText;

    navigator.clipboard.writeText(text).then(() => {
        button.innerText = 'Copied!';
        setTimeout(() => {
            button.innerText = 'Copy';
        }, 2000);
    }).catch(err => {
        console.error('Copy failed', err);
    });
}
