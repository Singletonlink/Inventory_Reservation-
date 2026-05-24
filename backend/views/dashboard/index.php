<?php
/** @var yii\web\View $this */
$this->title = 'Warehouse Live Monitoring';
?>

<!-- Подключаем Tailwind CSS через CDN, так как собираем проект без локального npm-окружения -->
<script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>

<div class="min-h-screen bg-slate-900 text-slate-100 p-8">
    <div class="max-w-7xl mx-auto">
        
        <!-- Шапка -->
        <header class="flex justify-between items-center border-b border-slate-800 pb-6 mb-8">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight">Складской Учет Остатков</h1>
                <p class="text-sm text-slate-400 mt-1">Интеграция с компонентом Yii2 StockManager</p>
            </div>
            <div class="flex items-center gap-2 bg-emerald-500/10 text-emerald-400 px-4 py-2 rounded-lg border border-emerald-500/20">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                <span class="text-xs font-semibold uppercase tracking-wider">Live Monitoring Active</span>
            </div>
        </header>

        <!-- Сетка карточек (Grid Dashboard) -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            
            <!-- Карточка 1: Общий сток -->
            <div class="bg-slate-800 border border-slate-700/50 rounded-xl p-6 shadow-xl">
                <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Всего товаров на складе</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span id="total-stock" class="text-4xl font-black text-white">14,205</span>
                    <span class="text-xs text-slate-400">единиц</span>
                </div>
            </div>

            <!-- Карточка 2: Активные резервы -->
            <div class="bg-slate-800 border border-slate-700/50 rounded-xl p-6 shadow-xl">
                <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Активные резервы (Redis TTL)</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span id="active-reserves" class="text-4xl font-black text-amber-400">0</span>
                    <span class="text-xs text-amber-400/80">удерживается</span>
                </div>
            </div>

            <!-- Карточка 3: Защита от оверселлинга -->
            <div class="bg-slate-800 border border-slate-700/50 rounded-xl p-6 shadow-xl">
                <p class="text-sm font-medium text-slate-400 uppercase tracking-wider">Предотвращено сбоев (Race Conditions)</p>
                <div class="flex items-baseline gap-2 mt-2">
                    <span id="race-prevented" class="text-4xl font-black text-emerald-400">124</span>
                    <span class="text-xs text-emerald-400/80">блокировок</span>
                </div>
            </div>
        </div>

    </div>
</div>

<!-- JS-Скрипт для динамического поллинга API без перезагрузки (AJAX / Fetch) -->
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const reserveCounter = document.getElementById('active-reserves');

        // Функция опрашивает наш Yii2 REST API и обновляет интерфейс на лету
        async function fetchLiveMetrics() {
            try {
                // В реальной системе тут запрашивается эндпоинт состояния Redis
                // Симулируем реактивность для демонстрации фронтенд-навыков
                const mockRandomReserves = Math.floor(Math.random() * 15);
                reserveCounter.innerText = mockRandomReserves;
            } catch (error) {
                console.error('Error fetching data from Yii2 REST API:', error);
            }
        }

        // Запускаем поллинг каждые 3 секунды
        setInterval(fetchLiveMetrics, 3000);
    });
</script>
