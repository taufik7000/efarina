import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Keuangan/**/*.php',
        './resources/views/filament/keuangan/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        './resources/views/filament/team/**/*.blade.php',
    ],
}
