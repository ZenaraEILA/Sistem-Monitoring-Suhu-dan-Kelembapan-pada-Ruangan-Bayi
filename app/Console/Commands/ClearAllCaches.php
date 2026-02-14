<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class ClearAllCaches extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cache:clear-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clear semua cache: application, route, config, view, compiled classes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            // 1. Clear application cache
            $this->call('cache:clear');
            $this->info('✅ Cache aplikasi sudah di-clear');
            
            // 2. Clear route cache
            $this->call('route:clear');
            $this->info('✅ Cache route sudah di-clear');
            
            // 3. Clear config cache
            $this->call('config:clear');
            $this->info('✅ Cache config sudah di-clear');
            
            // 4. Clear compiled classes
            $this->call('clear-compiled');
            $this->info('✅ Compiled classes sudah di-clear');
            
            // 5. Clear view cache (jika ada)
            if (method_exists($this, 'callSilent')) {
                $this->callSilent('view:clear');
                $this->info('✅ Cache view sudah di-clear');
            }
            
            // 6. Optimize autoloader
            $this->call('optimize');
            $this->info('✅ Autoloader di-optimize');
            
            $this->info('');
            $this->info('🎉 SEMUA CACHE BERHASIL DI-CLEAR!');
            $this->info('');
            $this->info('Tip: Jika grafik masih menampilkan data lama di browser:');
            $this->info('  1. Lakukan Hard Refresh: Ctrl+Shift+R (Windows) atau Cmd+Shift+R (Mac)');
            $this->info('  2. Buka DevTools: F12 → Application → Clear Site Data');
            $this->info('  3. Refresh halaman');
            $this->info('');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Error: ' . $e->getMessage());
            return 1;
        }
    }
}
