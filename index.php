<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slovník pojmů</title>
    <style>
        body { font-family: sans-serif; max-width: 900px; margin: 20px auto; padding: 0 10px; background: #f4f4f9; }
        .search-box { width: 100%; padding: 12px; font-size: 18px; box-sizing: border-box; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 6px; }
        table { width: 100%; border-collapse: collapse; background: #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #007bff; color: white; position: sticky; top: 0; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .status { text-align: center; padding: 10px; color: #666; font-style: italic; }
    </style>
</head>
<body>

    <h2>Vyhledávání v databázi</h2>
    <input type="text" id="searchInput" class="search-box" placeholder="Napište alespoň 2 znaky..." autofocus>

    <table>
        <thead>
            <tr>
                <th>EN Pojem</th>
                <th>EN Popis</th>
                <th>CZ Pojem</th>
                <th>CZ Popis</th>
            </tr>
        </thead>
        <tbody id="resultsTable">
            <!-- Sem se dynamicky vkládají řádky -->
        </tbody>
    </table>

    <div id="status" class="status"></div>

    <script>
        let query = '';
        let page = 1;
        let isLoading = false;
        let hasMore = true;

        const input = document.getElementById('searchInput');
        const tableBody = document.getElementById('resultsTable');
        const status = document.getElementById('status');

        // Odchycení psaní do vyhledávače (s lehkou pauzou/debounce)
        let timeout = null;
        input.addEventListener('input', () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                query = input.value.trim();
                page = 1;
                hasMore = true;
                tableBody.innerHTML = ''; // Vyčistit tabulku při novém hledání
                
                if (query.length >= 2) {
                    loadData();
                } else {
                    status.innerText = 'Napište alespoň 2 znaky...';
                }
            }, 300); // Čeká 300ms po dokončení psaní
        });

        // Funkce pro načtení dat z serveru
        async function loadData() {
            if (isLoading || !hasMore) return;
            isLoading = true;
            status.innerText = 'Načítám data...';

            try {
                const response = await fetch(`search.php?q=${encodeURIComponent(query)}&page=${page}`);
                const data = await response.json();

                if (data.length === 0) {
                    hasMore = false;
                    if (page === 1) {
                        status.innerText = 'Nebyly nalezeny žádné výsledky.';
                    } else {
                        status.innerText = 'Konec výsledků.';
                    }
                } else {
                    data.forEach(row => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `
                            <td><strong>${escapeHtml(row.en_pojem)}</strong></td>
                            <td>${escapeHtml(row.en_popis || '')}</td>
                            <td><strong>${escapeHtml(row.cz_pojem)}</strong></td>
                            <td>${escapeHtml(row.cz_popis || '')}</td>
                        `;
                        tableBody.appendChild(tr);
                    });
                    
                    page++;
                    status.innerText = '';
                }
            } catch (error) {
                status.innerText = 'Chyba při načítání dat.';
            } finally {
                isLoading = false;
            }
        }

        // Infinite Scroll - Detekce doskrolovaní na spodní část stránky
        window.addEventListener('scroll', () => {
            if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 200) {
                if (query.length >= 2 && hasMore && !isLoading) {
                    loadData();
                }
            }
        });

        // Ochrana proti XSS
        function escapeHtml(str) {
            return str.replace(/[&<>"']/g, match => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
            }[match]));
        }
    </script>

</body>
</html>