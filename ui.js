/* Progressive enhancements only. The forms and result pages work without JavaScript. */
(() => {
  'use strict';
  const root = document.documentElement;
  let theme = 'dark';
  try { if (localStorage.getItem('php-utilities-theme') === 'light') theme = 'light'; } catch (_) {}
  root.dataset.theme = theme;
  const syncTheme = () => {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
      button.setAttribute('aria-label', `Switch to ${root.dataset.theme === 'light' ? 'dark' : 'light'} theme`);
      const text = button.querySelector('.theme-label');
      if (text) text.textContent = root.dataset.theme === 'light' ? 'Dark mode' : 'Light mode';
    });
  };
  syncTheme();
  document.querySelectorAll('[data-theme-toggle]').forEach((button) => button.addEventListener('click', () => {
    root.dataset.theme = root.dataset.theme === 'light' ? 'dark' : 'light';
    try { localStorage.setItem('php-utilities-theme', root.dataset.theme); } catch (_) {}
    syncTheme();
  }));
  document.querySelectorAll('[data-filter]').forEach((field) => {
    const id = field.getAttribute('data-filter');
    const container = id ? document.getElementById(id) : null;
    if (!container) return;
    const items = Array.from(container.querySelectorAll('[data-search-item]'));
    const status = document.getElementById(id + '-count');
    const empty = document.getElementById(id + '-empty');
    const update = () => {
      const search = field.value.trim().toLocaleLowerCase();
      let shown = 0;
      for (const item of items) {
        const match = item.textContent.toLocaleLowerCase().includes(search);
        item.hidden = !match;
        if (match) shown++;
      }
      if (status) status.textContent = `${shown} / ${items.length} shown`;
      if (empty) empty.hidden = shown !== 0;
    };
    field.addEventListener('input', update);
    update();
  });
  document.querySelectorAll('[data-copy]').forEach((button) => button.addEventListener('click', async () => {
    const text = button.getAttribute('data-copy') || '';
    try {
      if (!navigator.clipboard || !window.isSecureContext) throw Error('Clipboard unavailable');
      await navigator.clipboard.writeText(text);
      const old = button.textContent;
      button.textContent = 'Copied ✓';
      setTimeout(() => { button.textContent = old; }, 1700);
    } catch (_) {
      button.textContent = 'Copy unavailable';
    }
  }));
  document.querySelectorAll('form[data-submit-state]').forEach((form) => form.addEventListener('submit', () => {
    const button = form.querySelector('button[type="submit"]');
    if (button) { button.disabled = true; button.setAttribute('aria-busy', 'true'); button.textContent = 'Working…'; }
  }));
  const input = document.getElementById('upload-file');
  if (input) {
    const dropzone = document.getElementById('upload-dropzone');
    const preview = document.getElementById('upload-preview');
    const message = document.getElementById('upload-feedback');
    let blobUrl = null;
    const update = () => {
      if (blobUrl) { URL.revokeObjectURL(blobUrl); blobUrl = null; }
      if (!preview || !message) return;
      preview.replaceChildren();
      const file = input.files && input.files[0];
      if (!file) { message.textContent = 'JPEG, PNG or GIF · maximum 500 KB'; return; }
      if (file.size > 500000 || !['image/jpeg','image/png','image/gif'].includes(file.type)) {
        message.textContent = 'Unsupported file or larger than 500 KB. Select a JPEG, PNG or GIF.';
        return;
      }
      message.textContent = 'File selected. Ready to upload.';
      const info = document.createElement('div');
      const label = document.createElement('strong'); label.textContent = file.name;
      const size = document.createElement('span'); size.textContent = `${(file.size / 1024).toFixed(1)} KB`;
      info.append(label, size);
      const image = document.createElement('img'); image.alt = 'Preview of selected image';
      blobUrl = URL.createObjectURL(file); image.src = blobUrl;
      preview.append(image, info); preview.hidden = false;
    };
    input.addEventListener('change', update);
    if (dropzone) {
      for (const eventName of ['dragenter', 'dragover']) dropzone.addEventListener(eventName, (e) => {
        e.preventDefault(); dropzone.classList.add('dragging');
      });
      for (const eventName of ['dragleave', 'drop']) dropzone.addEventListener(eventName, (e) => {
        e.preventDefault(); dropzone.classList.remove('dragging');
      });
      dropzone.addEventListener('drop', (event) => {
        if (!event.dataTransfer || !event.dataTransfer.files.length) return;
        try { input.files = event.dataTransfer.files; update(); } catch (_) { message.textContent = 'Please select your file with the file picker.'; }
      });
    }
  }
})();
