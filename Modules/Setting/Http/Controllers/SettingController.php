<?php

namespace Modules\Setting\Http\Controllers;

use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Modules\Setting\Entities\Setting;
use Modules\Setting\Http\Requests\StoreSettingsRequest;
use Modules\Setting\Http\Requests\StoreSmtpSettingsRequest;

class SettingController extends Controller
{

    public function index() {
        abort_if(Gate::denies('access_settings'), 403);

        $settings = Setting::firstOrFail();

        return view('setting::index', compact('settings'));
    }


    public function update(StoreSettingsRequest $request) {
        $settings = Setting::firstOrFail();
        $updateData = [
            'company_name' => $request->company_name,
            'company_email' => $request->company_email,
            'company_phone' => $request->company_phone,
            'notification_email' => $request->notification_email,
            'company_address' => $request->company_address,
            'default_currency_id' => $request->default_currency_id,
            'default_currency_position' => $request->default_currency_position,
        ];

        // Handle site logo upload
        if ($request->hasFile('site_logo')) {
            try {
                // Debug upload
                \Log::info('Site logo upload attempt', [
                    'file_size' => $request->file('site_logo')->getSize(),
                    'mime_type' => $request->file('site_logo')->getMimeType(),
                    'original_name' => $request->file('site_logo')->getClientOriginalName()
                ]);
                
                // Delete old logo if exists
                if ($settings->site_logo && \Storage::disk('public')->exists($settings->site_logo)) {
                    \Storage::disk('public')->delete($settings->site_logo);
                    \Log::info('Old site logo deleted: ' . $settings->site_logo);
                }
                
                // Ensure logos directory exists
                $logoPath = storage_path('app/public/logos');
                if (!file_exists($logoPath)) {
                    mkdir($logoPath, 0755, true);
                    \Log::info('Created logos directory: ' . $logoPath);
                }
                
                // Store new logo with timestamp
                $extension = $request->file('site_logo')->getClientOriginalExtension();
                $filename = 'site_logo_' . time() . '.' . $extension;
                $stored = $request->file('site_logo')->storeAs('logos', $filename, 'public');
                
                if ($stored) {
                    $updateData['site_logo'] = $stored;
                    \Log::info('Site logo uploaded successfully: ' . $stored);
                } else {
                    \Log::error('Failed to store site logo');
                }
            } catch (\Exception $e) {
                \Log::error('Site logo upload error: ' . $e->getMessage());
            }
        }

        // Handle login logo upload
        if ($request->hasFile('login_logo')) {
            try {
                // Debug upload
                \Log::info('Login logo upload attempt', [
                    'file_size' => $request->file('login_logo')->getSize(),
                    'mime_type' => $request->file('login_logo')->getMimeType(),
                    'original_name' => $request->file('login_logo')->getClientOriginalName()
                ]);
                
                // Delete old logo if exists
                if ($settings->login_logo && \Storage::disk('public')->exists($settings->login_logo)) {
                    \Storage::disk('public')->delete($settings->login_logo);
                    \Log::info('Old login logo deleted: ' . $settings->login_logo);
                }
                
                // Ensure logos directory exists
                $logoPath = storage_path('app/public/logos');
                if (!file_exists($logoPath)) {
                    mkdir($logoPath, 0755, true);
                    \Log::info('Created logos directory: ' . $logoPath);
                }
                
                // Store new logo with timestamp
                $extension = $request->file('login_logo')->getClientOriginalExtension();
                $filename = 'login_logo_' . time() . '.' . $extension;
                $stored = $request->file('login_logo')->storeAs('logos', $filename, 'public');
                
                if ($stored) {
                    $updateData['login_logo'] = $stored;
                    \Log::info('Login logo uploaded successfully: ' . $stored);
                } else {
                    \Log::error('Failed to store login logo');
                }
            } catch (\Exception $e) {
                \Log::error('Login logo upload error: ' . $e->getMessage());
            }
        }

        $settings->update($updateData);

        cache()->forget('settings');

        toast('Settings Updated!', 'info');

        return redirect()->route('settings.index');
    }


    public function updateSmtp(StoreSmtpSettingsRequest $request) {
        $toReplace = array(
            'MAIL_MAILER='.env('MAIL_HOST'),
            'MAIL_HOST="'.env('MAIL_HOST').'"',
            'MAIL_PORT='.env('MAIL_PORT'),
            'MAIL_FROM_ADDRESS="'.env('MAIL_FROM_ADDRESS').'"',
            'MAIL_FROM_NAME="'.env('MAIL_FROM_NAME').'"',
            'MAIL_USERNAME="'.env('MAIL_USERNAME').'"',
            'MAIL_PASSWORD="'.env('MAIL_PASSWORD').'"',
            'MAIL_ENCRYPTION="'.env('MAIL_ENCRYPTION').'"'
        );

        $replaceWith = array(
            'MAIL_MAILER='.$request->mail_mailer,
            'MAIL_HOST="'.$request->mail_host.'"',
            'MAIL_PORT='.$request->mail_port,
            'MAIL_FROM_ADDRESS="'.$request->mail_from_address.'"',
            'MAIL_FROM_NAME="'.$request->mail_from_name.'"',
            'MAIL_USERNAME="'.$request->mail_username.'"',
            'MAIL_PASSWORD="'.$request->mail_password.'"',
            'MAIL_ENCRYPTION="'.$request->mail_encryption.'"');

        try {
            file_put_contents(base_path('.env'), str_replace($toReplace, $replaceWith, file_get_contents(base_path('.env'))));
            Artisan::call('cache:clear');

            toast('Mail Settings Updated!', 'info');
        } catch (\Exception $exception) {
            Log::error($exception);
            session()->flash('settings_smtp_message', 'Something Went Wrong!');
        }

        return redirect()->route('settings.index');
    }
}
