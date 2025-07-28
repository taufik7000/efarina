<?php

namespace App\Filament\Team\Pages;

use App\Models\EmployeeProfile;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Auth;

class EditProfile extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';
    protected static ?string $navigationGroup = 'Profile Saya';
    protected static ?string $navigationLabel = 'Edit Profile';
    protected static ?string $title = 'Edit Profile';
    protected static string $view = 'filament.team.pages.edit-profile';

    public ?array $data = [];

    public function mount(): void
    {
        // Muat data profil user yang login, atau buat baru jika belum ada.
        $profile = Auth::user()->profile()->firstOrCreate([]);
        $this->form->fill($profile->toArray());
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Informasi Pribadi untuk ' . Auth::user()->name)
                    ->description('Lengkapi data pribadi Anda sesuai KTP.')
                    ->schema([
                        // Input untuk foto profi

                        TextInput::make('nik_ktp')
                            ->label('NIK (Nomor Induk Kependudukan)')
                            ->required(),
                        Grid::make(2)->schema([
                            TextInput::make('tempat_lahir')
                                ->label('Tempat Lahir'),
                            DatePicker::make('tanggal_lahir')
                                ->label('Tanggal Lahir'),
                        ]),
                        Grid::make(2)->schema([
                            Select::make('jenis_kelamin')
                                ->label('Jenis Kelamin')
                                ->options(EmployeeProfile::getGenderOptions()), // Mengambil dari static method di model
                            Select::make('agama')
                                ->label('Agama')
                                ->options(EmployeeProfile::getReligionOptions()),
                        ]),
                        Select::make('status_nikah')
                            ->label('Status Pernikahan')
                            ->options(EmployeeProfile::getMaritalStatusOptions()),
                    ]),

                Section::make('Informasi Kontak & Alamat')
                    ->schema([
                        TextInput::make('no_telepon')
                            ->label('Nomor Telepon')
                            ->tel(),
                        Textarea::make('alamat')
                            ->label('Alamat Lengkap Sesuai KTP')
                            ->rows(3),
                    ]),

                Section::make('Kontak Darurat')
                    ->description('Informasi kontak yang dapat dihubungi dalam keadaan darurat.')
                    ->schema([
                        Grid::make(2)->schema([
                            TextInput::make('kontak_darurat_nama')
                                ->label('Nama Kontak Darurat'),
                            TextInput::make('kontak_darurat_telp')
                                ->label('Telepon Kontak Darurat')
                                ->tel(),
                        ]),
                        TextInput::make('kontak_darurat_hubungan')
                            ->label('Hubungan dengan Kontak Darurat'),
                    ]),
                
                Section::make('Informasi Keuangan')
                    ->description('Data ini bersifat rahasia dan hanya akan dilihat oleh HRD.')
                    ->schema([
                        TextInput::make('npwp')
                            ->label('Nomor NPWP'),
                        TextInput::make('no_rekening')
                            ->label('Nomor Rekening Bank'),
                    ]),
            ])
            ->statePath('data')
            ->model(EmployeeProfile::class);
    }

    public function updateProfile(): void
    {
        try {
            $data = $this->form->getState();
            Auth::user()->profile()->update($data);
            Auth::user()->touch(); 

            Notification::make()
                ->title('Profil berhasil diperbarui')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Gagal memperbarui profil')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}