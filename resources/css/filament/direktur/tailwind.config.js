import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Direktur/**/*.php',
        './resources/views/filament/direktur/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        './resources/views/filament/team/**/*.blade.php',
    ],
}
