import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Team/**/*.php',
        './resources/views/filament/team/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
}
