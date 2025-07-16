import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Redaksi/**/*.php',
        './resources/views/filament/redaksi/**/*.blade.php',
        './vendor/filament/**/*.blade.php',

        './resources/views/filament/team/**/*.blade.php',
    ],
}
