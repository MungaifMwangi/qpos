<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Rules\ValidImageType;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Artisan;
use App\Trait\FileHandler;

class WebsiteSettingController extends Controller
{
    public $fileHandler;

    public function __construct(FileHandler $fileHandler)
    {
        $this->fileHandler = $fileHandler;
    }

    public function websiteGeneral(Request $request)
    {
        return view('backend.settings.website-settings.general');
    }

    public function websiteInfoUpdate(Request $request)
    {
        $request->validate([
            'site_name' => 'required',
            'site_url' => 'url'
        ]);

        foreach ($request->except('_token') as $key => $value) {
            writeConfig($key, $value);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'website-info'])
            ->with('success', 'Updated successfully');
    }

    public function websiteContactsUpdate(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            writeConfig($key, $value);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'contacts'])
            ->with('success', 'Updated successfully');
    }

    public function websiteSocialLinkUpdate(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            writeConfig($key, $value);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'social-links'])
            ->with('success', 'Updated successfully');
    }

    public function websiteStyleSettingsUpdate(Request $request)
    {
        $request->validate([
            'site_logo' => ['file', new ValidImageType],
            'favicon_icon' => ['file', new ValidImageType],
            'favicon_icon_apple' => ['file', new ValidImageType],
        ]);

        writeConfig('newsletter_subscribe', $request->newsletter_subscribe);

        if ($request->hasFile("site_logo")) {
            $this->fileHandler->securePublicUnlink(readConfig('site_logo'));
            $site_logo = $this->fileHandler->uploadToPublic($request->file("site_logo"), "/assets/images/logo");
            writeConfig('site_logo', $site_logo);
        }
        if ($request->hasFile("favicon_icon")) {
            $this->fileHandler->securePublicUnlink(readConfig('favicon_icon'));
            $favicon_icon = $this->fileHandler->uploadToPublic($request->file("favicon_icon"), "/assets/images/logo");
            writeConfig('favicon_icon', $favicon_icon);
        }
        if ($request->hasFile("favicon_icon_apple")) {
            $this->fileHandler->securePublicUnlink(readConfig('favicon_icon_apple'));
            $favicon_icon_apple = $this->fileHandler->uploadToPublic($request->file("favicon_icon_apple"), "/assets/images/logo");
            writeConfig('favicon_icon_apple', $favicon_icon_apple);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'style-settings'])
            ->with('success', 'Updated successfully');
    }

    public function websiteCustomCssUpdate(Request $request)
    {
        writeConfig('custom_css', $request->custom_css);
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'custom-css'])
            ->with('success', 'Updated successfully');
    }

    public function websiteNotificationSettingsUpdate(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            writeConfig($key, $value);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'notification-settings'])
            ->with('success', 'Updated successfully');
    }
    

    public function websiteStatusUpdate(Request $request)
    {
        writeConfig('is_live', $request->is_live);
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'website-status'])
            ->with('success', 'Updated successfully');
    }
    public function websiteInvoiceUpdate(Request $request)
    {
        foreach ($request->except('_token') as $key => $value) {
            writeConfig($key, $value);
        }
        Artisan::call('config:clear');
        return to_route('backend.admin.settings.website.general', ['active-tab' => 'invoice-settings'])
            ->with('success', 'Updated successfully');
    }

    public function getCurrentVersion()
    {
        $base = base_path();
        $log = trim(shell_exec("git -C \"{$base}\" log -1 --pretty=format:'%s' 2>&1") ?? '');
        $hash = trim(shell_exec("git -C \"{$base}\" rev-parse --short HEAD 2>&1") ?? '');
        $version = 'Unknown';
        if (preg_match('/Version\s+([\d.]+)/i', $log, $m)) {
            $version = $m[1];
        }
        return ['version' => $version, 'commit' => $hash, 'message' => $log];
    }

    public function checkForUpdate()
    {
        abort_if(!auth()->user()->can('system_update_settings'), 403);

        $base = base_path();
        $branch = trim(shell_exec("git -C \"{$base}\" rev-parse --abbrev-ref HEAD 2>&1") ?? 'main');

        shell_exec("git -C \"{$base}\" fetch origin 2>&1");

        $localHash  = trim(shell_exec("git -C \"{$base}\" rev-parse HEAD 2>&1") ?? '');
        $remoteHash = trim(shell_exec("git -C \"{$base}\" rev-parse origin/{$branch} 2>&1") ?? '');

        $localCommitMsg  = trim(shell_exec("git -C \"{$base}\" log -1 --pretty=format:'%s' 2>&1") ?? '');
        $remoteCommitMsg = trim(shell_exec("git -C \"{$base}\" log -1 --pretty=format:'%s' origin/{$branch} 2>&1") ?? '');

        $localVersion = 'Unknown';
        if (preg_match('/Version\s+([\d.]+)/i', $localCommitMsg, $m)) {
            $localVersion = $m[1];
        }
        $remoteVersion = 'Unknown';
        if (preg_match('/Version\s+([\d.]+)/i', $remoteCommitMsg, $m)) {
            $remoteVersion = $m[1];
        }

        $updatable = strtolower($localHash) !== strtolower($remoteHash);

        $behind = 0;
        if ($updatable) {
            $behind = (int) trim(shell_exec("git -C \"{$base}\" rev-list --count HEAD..origin/{$branch} 2>&1") ?? '0');
        }

        return response()->json([
            'updatable'      => $updatable,
            'local_version'  => $localVersion,
            'remote_version' => $remoteVersion,
            'local_hash'     => substr($localHash, 0, 7),
            'remote_hash'    => substr($remoteHash, 0, 7),
            'behind'         => $behind,
            'branch'         => $branch,
        ]);
    }

    public function applyUpdate()
    {
        abort_if(!auth()->user()->can('system_update_settings'), 403);

        $base = base_path();
        $output = [];
        $exitCode = 0;

        // SAFETY: Only migrate (schema changes). NEVER run db:seed.
        // Seeders contain dummy/demo data that must not overwrite client data.
        $commands = [
            ['label' => 'Pulling latest changes from git...',    'cmd' => "git -C \"{$base}\" pull origin 2>&1"],
            ['label' => 'Installing Composer dependencies...',   'cmd' => "composer install --no-dev --optimize-autoloader --no-interaction 2>&1", 'workdir' => $base],
            ['label' => 'Installing NPM dependencies...',        'cmd' => "npm install --no-optional 2>&1", 'workdir' => $base],
            ['label' => 'Building frontend assets...',           'cmd' => "npm run build 2>&1", 'workdir' => $base],
            ['label' => 'Running database migrations (schema only — no seeders)...', 'cmd' => "php artisan migrate --force 2>&1", 'workdir' => $base],
            ['label' => 'Clearing application cache...',         'cmd' => "php artisan optimize:clear 2>&1", 'workdir' => $base],
            ['label' => 'Resetting permission cache...',         'cmd' => "php artisan permission:cache-reset 2>&1", 'workdir' => $base],
        ];

        foreach ($commands as $step) {
            $output[] = "==> {$step['label']}";
            $workdir = $step['workdir'] ?? $base;
            $result = [];
            exec("cd \"{$workdir}\" && {$step['cmd']}", $result, $exitCode);
            $output[] = implode("\n", $result);
            $output[] = '';
        }

        $versionInfo = $this->getCurrentVersion();

        return response()->json([
            'success' => true,
            'log'     => implode("\n", $output),
            'version' => $versionInfo['version'],
            'commit'  => $versionInfo['commit'],
        ]);
    }
}
