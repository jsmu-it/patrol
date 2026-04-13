<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Kraepelin - {{ $session->participant_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .number-grid {
            display: flex;
            gap: 2px;
        }
        .column {
            display: flex;
            flex-direction: column;
            align-items: center;
            border-left: 1px solid #e5e7eb;
            border-right: 1px solid #e5e7eb;
        }
        .number-cell {
            width: 36px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
            color: #1e3a8a;
            background: #f8fafc;
        }
        .answer-input {
            width: 36px;
            height: 24px;
            border: 1px solid #cbd5e1;
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            background: white;
            outline: none;
        }
        .answer-input:focus {
            border-color: #8b5cf6;
            background: #faf5ff;
        }
        .column-header {
            font-size: 10px;
            color: #9ca3af;
            padding: 4px;
        }
        .active-column {
            background: #fef3c7;
        }
        .completed-column .answer-input {
            background: #f0fdf4;
            border-color: #86efac;
        }
    </style>
</head>
<body class="min-h-screen bg-gray-100">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Test Kraepelin</h1>
                    <p class="text-sm text-gray-500">{{ $session->participant_name }}</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-center">
                        <div id="timer" class="text-2xl font-mono font-bold text-purple-600">00:00</div>
                        <div class="text-xs text-gray-500">Waktu Tersisa</div>
                    </div>
                    <div class="text-center">
                        <div id="current-col" class="text-xl font-bold text-gray-900">1</div>
                        <div class="text-xs text-gray-500">Kolom / {{ $questionSet->column_count }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructions Modal --}}
    <div id="instructions-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Petunjuk Test Kraepelin</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <p><strong>1.</strong> Jumlahkan dua angka yang berdekatan secara vertikal.</p>
                <p><strong>2.</strong> Jika hasil >= 10, tulis hanya digit satuan (contoh: 7+8=15, tulis 5).</p>
                <p><strong>3.</strong> Kerjakan kolom per kolom, dari atas ke bawah.</p>
                <p><strong>4.</strong> Setiap kolom memiliki waktu <strong>{{ $questionSet->time_per_column_seconds }} detik</strong>.</p>
                <p><strong>5.</strong> Setelah waktu habis, akan otomatis pindah ke kolom berikutnya.</p>
                <p><strong>6.</strong> Kerjakan secepat dan seteliti mungkin!</p>
            </div>
            <div class="mt-6 bg-blue-50 border border-blue-100 rounded-lg p-4">
                <p class="text-sm text-blue-800">
                    <strong>Contoh:</strong> Jika ada angka 4 dan 7 yang berdekatan, maka 4 + 7 = 11, tulis <strong>1</strong>.
                </p>
            </div>
            <button onclick="startTest()" class="mt-6 w-full py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                Mulai Test
            </button>
        </div>
    </div>

    {{-- Test Area --}}
    <div id="test-area" class="hidden py-6">
        <form id="test-form" action="{{ route('psikotest.submit', $session->access_token) }}" method="POST">
            @csrf
            <div class="max-w-7xl mx-auto px-4 overflow-x-auto pb-4">
                <div class="number-grid justify-center">
                    @php $columns = $questionSet->columns_data ?? []; @endphp
                    @foreach($columns as $colIndex => $numbers)
                    <div class="column" id="column-{{ $colIndex }}" data-col="{{ $colIndex }}">
                        <div class="column-header">{{ $colIndex + 1 }}</div>
                        @foreach($numbers as $rowIndex => $number)
                            <div class="number-cell">{{ $number }}</div>
                            @if($rowIndex < count($numbers) - 1)
                            <input type="text" 
                                   name="answers[{{ $colIndex }}][{{ $rowIndex }}]" 
                                   class="answer-input" 
                                   maxlength="1"
                                   pattern="[0-9]"
                                   data-col="{{ $colIndex }}"
                                   data-row="{{ $rowIndex }}"
                                   autocomplete="off"
                                   tabindex="-1">
                            @endif
                        @endforeach
                    </div>
                    @endforeach
                </div>
            </div>
        </form>
    </div>

    {{-- Completion Modal --}}
    <div id="complete-modal" class="fixed inset-0 bg-black bg-opacity-50 items-center justify-center z-50 p-4 hidden">
        <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 text-center">
            <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h2 class="text-xl font-bold text-gray-900 mb-2">Test Selesai!</h2>
            <p class="text-gray-600 mb-6">Terima kasih telah mengerjakan test. Hasil akan diproses oleh HRD.</p>
            <button onclick="submitTest()" class="w-full py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition">
                Kirim Jawaban
            </button>
        </div>
    </div>

    <script>
        const timePerColumn = {{ $questionSet->time_per_column_seconds }};
        const totalColumns = {{ $questionSet->column_count }};
        let currentColumn = 0;
        let timer = null;
        let timeLeft = timePerColumn;

        function startTest() {
            document.getElementById('instructions-modal').classList.add('hidden');
            document.getElementById('test-area').classList.remove('hidden');
            activateColumn(0);
            startTimer();
        }

        function activateColumn(colIndex) {
            // Deactivate all columns
            document.querySelectorAll('.column').forEach(col => {
                col.classList.remove('active-column');
                col.querySelectorAll('.answer-input').forEach(input => {
                    input.tabIndex = -1;
                });
            });

            if (colIndex >= totalColumns) {
                // Test completed
                showCompletionModal();
                return;
            }

            currentColumn = colIndex;
            const col = document.getElementById('column-' + colIndex);
            col.classList.add('active-column');
            
            // Enable inputs in this column
            const inputs = col.querySelectorAll('.answer-input');
            inputs.forEach((input, idx) => {
                input.tabIndex = idx;
            });

            // Focus first input
            if (inputs.length > 0) {
                inputs[0].focus();
            }

            // Scroll to column
            col.scrollIntoView({ behavior: 'smooth', inline: 'center' });

            document.getElementById('current-col').textContent = colIndex + 1;
            timeLeft = timePerColumn;
        }

        function startTimer() {
            timer = setInterval(() => {
                timeLeft--;
                const mins = Math.floor(timeLeft / 60);
                const secs = timeLeft % 60;
                document.getElementById('timer').textContent = 
                    String(mins).padStart(2, '0') + ':' + String(secs).padStart(2, '0');
                
                if (timeLeft <= 0) {
                    // Mark current column as completed
                    const col = document.getElementById('column-' + currentColumn);
                    col.classList.add('completed-column');
                    
                    // Move to next column
                    activateColumn(currentColumn + 1);
                }
            }, 1000);
        }

        function showCompletionModal() {
            clearInterval(timer);
            document.getElementById('complete-modal').classList.remove('hidden');
            document.getElementById('complete-modal').classList.add('flex');
        }

        function submitTest() {
            document.getElementById('test-form').submit();
        }

        // Auto-move to next input on entry
        document.addEventListener('input', function(e) {
            if (e.target.classList.contains('answer-input')) {
                const value = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = value.slice(-1); // Keep only last digit
                
                if (value) {
                    const col = parseInt(e.target.dataset.col);
                    const row = parseInt(e.target.dataset.row);
                    
                    // Find next input in same column
                    const nextInput = document.querySelector(`input[data-col="${col}"][data-row="${row + 1}"]`);
                    if (nextInput) {
                        nextInput.focus();
                    }
                }
            }
        });
    </script>
</body>
</html>
