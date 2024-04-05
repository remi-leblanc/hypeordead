# HypeOrDead

https://hod.remileblanc.fr

This project is unmaintained and the ranking is not updated anymore.

HypeOrDead is a website that provides a popularity ranking of multiplayer games. The score of each game is updated each week and calculated with data from APIs of Reddit, Twitch, Discord.

To ensure the best accuracy possible, data is fetched every hour to reflect the games variable popularity during the day and timezones. Then once a week, it average those values and store them in the database.

The final score isn't saved directly in the database, only the raw values are. The score of each week is calculated on the fly. This way, we can change the score calculation formula and keep the whole history consistent.
