# Retro Tape Deck MP3 Player

A WordPress plugin that renders a fully functional MP3 player styled as an early-1990s dual cassette tape deck. Drop the `[retro_tape_deck]` shortcode into any page or post.

## Features

- Dual cassette well design (Deck A for playback, Deck B shows the playlist)
- Spinning tape reels during playback
- Animated stereo VU meters (L/R) using the Web Audio API
- LCD-style green-on-black track display with elapsed time
- Full transport controls: Play, Pause, Stop, Rewind, Fast Forward, Previous, Next, Eject
- Volume slider and seek/progress bar
- Expandable playlist drawer
- Admin settings page for managing tracks, brand name, and model name
- Responsive layout for mobile
- Decorative details: screws, LED indicator, gold branding, Dolby B NR footer

## Installation

1. Copy the `retro-tape-deck-player/` folder into `wp-content/plugins/`.
2. Activate **Retro Tape Deck MP3 Player** in the WordPress admin under Plugins.
3. Go to **Settings > Tape Deck Player** and add your tracks as JSON.
4. Place `[retro_tape_deck]` on any page or post.

## Shortcode Usage

**Basic (uses tracks from settings):**

```
[retro_tape_deck]
```

**Inline tracks (overrides settings):**

```
[retro_tape_deck tracks='[{"title":"My Song","url":"https://example.com/song.mp3"}]']
```

**Custom branding:**

```
[retro_tape_deck brand="SoundWave" model="CX-990 Stereo"]
```

## Track JSON Format

```json
[
  { "title": "Track One", "url": "https://yoursite.com/wp-content/uploads/track1.mp3" },
  { "title": "Track Two", "url": "https://yoursite.com/wp-content/uploads/track2.mp3" }
]
```

## File Structure

```
retro-tape-deck-player/
  retro-tape-deck-player.php   Main plugin file
  includes/
    class-rtd-admin.php        Admin settings page
    class-rtd-shortcode.php    Shortcode renderer (HTML template)
  assets/
    css/tape-deck.css           All player styles
    js/tape-deck.js             Player engine & VU meter animation
```

## License

GPL-2.0-or-later
