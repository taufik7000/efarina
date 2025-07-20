import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Hrd/**/*.php',
        './resources/views/filament/hrd/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
