<x-guest-layout>
    <div>
        <!-- Heading -->
        <div style="margin-bottom:1.5rem;">
            <div style="font-size:0.65rem; font-weight:700; letter-spacing:0.14em; text-transform:uppercase; color:#e05252; margin-bottom:0.5rem;">Platform Keperawatan &middot; KADIN</div>
            <h1 style="font-family:'Playfair Display',serif; font-size:1.75rem; font-weight:700; color:#1a1a1a; margin:0 0 0.375rem; line-height:1.2;">Buat akun &amp; klaim profil</h1>
            <p style="font-size:0.875rem; color:#9a9a9a; margin:0;">Pilih peran yang sesuai. Perawat cukup masukkan NIK untuk menghubungkan ijazah kampus.</p>
        </div>

        <!-- Tips -->
        <div style="padding:0.875rem 1rem; border-radius:0.875rem; background:rgba(15,80,80,0.06); border:1px solid rgba(15,80,80,0.15); margin-bottom:1.5rem;">
            <div style="font-size:0.75rem; font-weight:700; color:#0f5050; margin-bottom:0.375rem;">💡 Tips Auto-Claim Profil</div>
            <div style="font-size:0.8rem; color:#5a5a5a; line-height:1.6;">Perawat: masukkan NIK 16 digit. Sistem akan otomatis mendeteksi ijazah &amp; data kampus Anda dari Kampus Gateway.</div>
        </div>

        <!-- Real-time NIK Claim Banner (Initially Hidden) -->
        <div id="nik-claim-banner" style="display:none; padding:1rem; border-radius:0.875rem; background:rgba(16,185,129,0.1); border:1px solid rgba(16,185,129,0.3); margin-bottom:1.5rem;" class="fade-up">
            <div style="display:flex; align-items:center; gap:0.625rem;">
                <div style="width:2rem; height:2rem; border-radius:0.5rem; background:#10b981; color:#ffffff; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                    ✓
                </div>
                <div>
                    <div style="font-size:0.75rem; font-weight:700; color:#065f46;">Data Kampus Ditemukan!</div>
                    <div id="nik-claim-info" style="font-size:0.8rem; color:#1a1a1a; margin-top:0.125rem;"></div>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register') }}" style="display:flex; flex-direction:column; gap:1rem;">
            @csrf

            <div>
                <x-input-label for="name" :value="__('Nama Lengkap')" />
                <x-text-input id="name" style="margin-top:0.375rem; width:100%;" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="Nama sesuai identitas / ijazah" />
                <x-input-error :messages="$errors->get('name')" class="mt-2" />
            </div>

            <div>
                <div style="display:flex; align-items:center; justify-content:space-between;">
                    <x-input-label for="nik" :value="__('NIK (khusus perawat)')" />
                    <span id="reg-nik-badge" class="badge badge-muted" style="font-size:0.65rem;">0 / 16 Digit</span>
                </div>
                <x-text-input id="nik" style="margin-top:0.375rem; width:100%; font-weight:700; letter-spacing:0.05em;" type="text" name="nik" :value="old('nik')" autocomplete="off" inputmode="numeric" maxlength="19" placeholder="3578 0000 0000 0001" />
                <p style="font-size:0.75rem; color:#9a9a9a; margin:0.375rem 0 0;">Kosongkan jika mendaftar sebagai Kampus/Mitra/Regulator.</p>
                <x-input-error :messages="$errors->get('nik')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="email" :value="__('Email')" />
                <x-text-input id="email" style="margin-top:0.375rem; width:100%;" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="role" :value="__('Peran')" />
                <select id="role" name="role" class="select-field" style="margin-top:0.375rem;" required>
                    <option value="" disabled {{ old('role') ? '' : 'selected' }}>Pilih peran</option>
                    <option value="perawat" @selected(old('role') === 'perawat' || !old('role'))>Perawat (Alumni / Fresh Graduate)</option>
                    <option value="kampus" @selected(old('role') === 'kampus')>Kampus / Perguruan Tinggi</option>
                    <option value="lembaga" @selected(old('role') === 'lembaga')>Lembaga Pelatihan &amp; Sertifikasi</option>
                    <option value="mitra" @selected(old('role') === 'mitra')>Mitra Pengguna (RS/Industri/Faskes)</option>
                    <option value="headhunter" @selected(old('role') === 'headhunter')>Headhunter / Agency</option>
                    <option value="regulator" @selected(old('role') === 'regulator')>Regulator (Kemenkes/KTKI/PPNI)</option>
                </select>
                <x-input-error :messages="$errors->get('role')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password" :value="__('Kata Sandi')" />
                <x-text-input id="password" style="margin-top:0.375rem; width:100%;" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div>
                <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" />
                <x-text-input id="password_confirmation" style="margin-top:0.375rem; width:100%;" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password" />
                <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
            </div>

            <div style="display:flex; flex-wrap:wrap; align-items:center; justify-content:space-between; gap:0.875rem; margin-top:0.25rem;">
                <a href="{{ route('login') }}" style="font-size:0.8rem; color:#9a9a9a; text-decoration:none;">Sudah punya akun? <span style="color:#e05252; font-weight:600;">Masuk</span></a>
                <x-primary-button>{{ __('Daftar Akun &rarr;') }}</x-primary-button>
            </div>
        </form>
    </div>

    <!-- Registration NIK Real-time Auto-Claim Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const nikInput = document.getElementById('nik');
            const nameInput = document.getElementById('name');
            const badge = document.getElementById('reg-nik-badge');
            const claimBanner = document.getElementById('nik-claim-banner');
            const claimInfo = document.getElementById('nik-claim-info');

            if (!nikInput || !badge) return;

            let debounceTimer;

            nikInput.addEventListener('input', function () {
                let raw = nikInput.value.replace(/[^0-9]/g, '');
                if (raw.length > 16) raw = raw.substring(0, 16);

                const digits = raw.length;
                if (digits === 16) {
                    badge.className = 'badge badge-teal';
                    badge.innerHTML = '✓ 16 Digit Valid';

                    // Trigger NIK Auto-Claim Lookup
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        fetch('/api/v1/kampus/gateway/' + raw)
                            .then(res => res.json())
                            .then(data => {
                                if (data && data.found) {
                                    claimBanner.style.display = 'block';
                                    claimInfo.innerHTML = '🎓 Data ditemukan: <strong>' + data.nama + '</strong> (' + data.institusi.nama + '). Mengdaftar akan otomatis menghubungkan ijazah!';
                                    if (nameInput && !nameInput.value) {
                                        nameInput.value = data.nama;
                                    }
                                } else {
                                    claimBanner.style.display = 'none';
                                }
                            })
                            .catch(() => { claimBanner.style.display = 'none'; });
                    }, 300);

                } else if (digits > 0) {
                    badge.className = 'badge badge-coral';
                    badge.innerHTML = digits + ' / 16 Digit';
                    claimBanner.style.display = 'none';
                } else {
                    badge.className = 'badge badge-muted';
                    badge.innerHTML = '0 / 16 Digit';
                    claimBanner.style.display = 'none';
                }
            });
        });
    </script>
</x-guest-layout>
