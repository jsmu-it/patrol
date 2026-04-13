<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Papikostik - {{ $session->participant_name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">
    {{-- Header --}}
    <div class="bg-white shadow-sm border-b sticky top-0 z-50">
        <div class="max-w-3xl mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-lg font-bold text-gray-900">Test Papikostik</h1>
                    <p class="text-sm text-gray-500">{{ $session->participant_name }}</p>
                </div>
                <div class="text-center">
                    <div id="progress" class="text-xl font-bold text-purple-600">0 / {{ count($questionSet->pairs ?? []) }}</div>
                    <div class="text-xs text-gray-500">Soal Dijawab</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Instructions Modal --}}
    <div id="instructions-modal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl max-w-lg w-full p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">Petunjuk Test Papikostik</h2>
            <div class="space-y-3 text-sm text-gray-600">
                <p><strong>1.</strong> Anda akan diberikan 90 pasangan pernyataan.</p>
                <p><strong>2.</strong> Pilih pernyataan yang <strong>paling menggambarkan diri Anda</strong>.</p>
                <p><strong>3.</strong> Tidak ada jawaban benar atau salah, jawab dengan jujur.</p>
                <p><strong>4.</strong> Kerjakan semua soal tanpa melewatkan satupun.</p>
                <p><strong>5.</strong> Tidak ada batasan waktu, tapi usahakan menjawab secara spontan.</p>
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
            <div class="max-w-3xl mx-auto px-4 space-y-4">
                @php $pairs = $questionSet->pairs ?? []; @endphp
                @foreach($pairs as $index => $pair)
                <div class="question-card bg-white rounded-xl shadow-sm border border-gray-200 p-6" data-index="{{ $index }}">
                    <div class="text-sm text-gray-400 mb-4">Soal {{ $index + 1 }} dari {{ count($pairs) }}</div>
                    
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition option-label">
                            <input type="radio" name="answers[{{ $index }}]" value="a" class="mt-1" onchange="updateProgress()">
                            <div>
                                <span class="font-medium text-purple-600">A.</span>
                                <span class="text-gray-700">{{ $pair['statement_a'] ?? '-' }}</span>
                            </div>
                        </label>
                        
                        <label class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-lg cursor-pointer hover:border-purple-300 hover:bg-purple-50 transition option-label">
                            <input type="radio" name="answers[{{ $index }}]" value="b" class="mt-1" onchange="updateProgress()">
                            <div>
                                <span class="font-medium text-purple-600">B.</span>
                                <span class="text-gray-700">{{ $pair['statement_b'] ?? '-' }}</span>
                            </div>
                        </label>
                    </div>
                </div>
                @endforeach

                <div class="pt-6 pb-12">
                    <button type="submit" id="submit-btn" disabled
                        class="w-full py-4 bg-purple-600 text-white font-semibold rounded-xl hover:bg-purple-700 transition disabled:bg-gray-300 disabled:cursor-not-allowed">
                        Kirim Jawaban
                    </button>
                    <p id="submit-msg" class="text-center text-sm text-gray-500 mt-2">Jawab semua soal untuk mengirim</p>
                </div>
            </div>
        </form>
    </div>

    <script>
        const totalQuestions = {{ count($pairs) }};

        function startTest() {
            document.getElementById('instructions-modal').classList.add('hidden');
            document.getElementById('test-area').classList.remove('hidden');
        }

        function updateProgress() {
            const answered = document.querySelectorAll('input[type="radio"]:checked').length;
            document.getElementById('progress').textContent = answered + ' / ' + totalQuestions;
            
            if (answered === totalQuestions) {
                document.getElementById('submit-btn').disabled = false;
                document.getElementById('submit-msg').textContent = 'Klik untuk mengirim jawaban';
            }
        }

        // Highlight selected option
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const card = this.closest('.question-card');
                card.querySelectorAll('.option-label').forEach(label => {
                    label.classList.remove('border-purple-500', 'bg-purple-50');
                });
                this.closest('.option-label').classList.add('border-purple-500', 'bg-purple-50');
            });
        });
    </script>
</body>
</html>
