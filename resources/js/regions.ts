
export function initRegionAutocomplete(): void {
    const regionInput = document.getElementById('region') as HTMLInputElement | null;
    const suggestionsBlock = document.getElementById('region-suggestions') as HTMLDivElement | null;
    if (regionInput && suggestionsBlock) {
        regionInput.addEventListener('input', async () => {
            const query = regionInput.value.trim();
            if (query.length < 2) {
                suggestionsBlock.classList.add('hidden');
                return;
            }
            try {
                const response = await fetch(`/api/regions?query=${encodeURIComponent(query)}`);
                if (!response.ok) {
                    const errorData = await response.json().catch(() => ({}));
                    console.error('Ошибка прокси-сервера Laravel:', response.status, errorData);
                    return;
                }
                const regions: string[] = await response.json();
                if (regions.length === 0) {
                    suggestionsBlock.classList.add('hidden');
                    return;
                }
                suggestionsBlock.innerHTML = '';
                suggestionsBlock.classList.remove('hidden');
                regions.forEach((regionName: string) => {
                    const btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'w-full text-left px-4 py-2.5 hover:bg-blue-50 text-sm text-slate-700 font-medium transition border-b border-slate-100 last:border-none';
                    btn.textContent = regionName;

                    btn.addEventListener('click', () => {
                        regionInput.value = regionName;
                        suggestionsBlock.classList.add('hidden');
                    });
                    suggestionsBlock.appendChild(btn);
                });
            } catch (error) {
                console.error('Сетевая ошибка фронтенда:', error);
            }
        });
        document.addEventListener('click', (e) => {
            if (e.target !== regionInput && e.target !== suggestionsBlock) {
                suggestionsBlock.classList.add('hidden');
            }
        });
    }
}
