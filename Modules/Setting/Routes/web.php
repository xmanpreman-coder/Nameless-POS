<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['middleware' => 'auth'], function () {

    //Mail Settings
    Route::patch('/settings/smtp', 'SettingController@updateSmtp')->name('settings.smtp.update');
    //General Settings
    Route::get('/settings', 'SettingController@index')->name('settings.index');
    Route::patch('/settings', 'SettingController@update')->name('settings.update');
    
    // Database Backup Routes
    Route::get('/database-backup', 'DatabaseBackupController@index')->name('database.backup.index');
    Route::get('/database-backup/download', 'DatabaseBackupController@download')->name('database.backup.download');
    Route::post('/database-backup/restore', 'DatabaseBackupController@restore')->name('database.backup.restore');
    Route::post('/database-backup/merge', 'DatabaseBackupController@merge')->name('database.backup.merge');
    Route::post('/database-backup/selective-restore', 'DatabaseBackupController@selectiveRestore')->name('database.backup.selectiveRestore');
    Route::post('/database-backup/analyze', 'DatabaseBackupController@analyze')->name('database.backup.analyze');
    
    // Units
    Route::resource('units', 'UnitsController')->except('show');
});
