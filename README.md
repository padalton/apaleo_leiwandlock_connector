![Apaleo LeiwandLock Connector](https://app.leiwand-lock.at/repository-open-graph-template.png)
# Apaleo <-> LeiwandLock Connector
## _The best way for modern Room-Access_

Leiwand Lock is a modern Access-/Locking-System with digital keys.
Every Lock-Unit is driven by custom developed electronics and operated by a Single-board-computer with a full Linux OS on it.

The "Locks" are online and always connected to the local LeiwandLock-Server, so every change on the reservation takes effect nearly instant.
For example the guest could book a room or a extension on his stay and after paying (or check in with Code2Order) he could instanty access the room.
For a room-change the guest doesnt need a new digital key as the key identifies only the guest and reservation to the system (no time or room informations).

## Features

- Self-Service setup (of connector) for the property
- Connect as many properties as you like
- Exclude rooms from LeiwandLock-functionality
- Guest gets digital key right after checkin via email
- Setup can be done in less than 10 Minutes (if LeiwandLock-Server is set up localy)

## Videos
- [demonstration video how it works in the hotel](https://youtu.be/rep8EwkXEIY) 
- [loom screen presentation of the setup](https://www.loom.com/share/23e892f278b046c0a50259873f3e7cfb) password is the name of my Advisor in Slack / or ask me ;)


## Requirements

As this is a Connector, you'll need a LeiwandLock infrastructure:

- [LeiwandLock Server] - Docker Container running Python/Django and some Bash-scripts
- [LeiwandLock Locks] - only one ethernet cable to the Lock-Unit, cheaper than most smart locks (and smarter)
- [Secure Network] - As secutity is very important for locks, we provide assistance setting up the network for LeiwandLock
- [Guest Self Service] - works best with a fully automated self service guest-journy (Apaleo,Code2Order,LeiwandLock)


**If you need help setting everything up or need access to a LeiwandLock-infrastructure for testing,**
**please [contact me](mailto:padalton86@gmail.com)**
For testing you can also use my setup at https://app.leiwand-lock.at/

## Installation

The Connector requires a Webserver with PHP >7.0 and MiraDB to run.
Required libaries are installed using composer.

Install the dependencies:

```
composer install
```

Copy config.example.php to config.php and change the config values acordingly. (apaleo Client, DB, endpoint)

## Development continues

Apaleo One / UI Integration, multi-language support, email-templates, email history and much more is planned and will come in the next weeks

## About

I am Stefan, 34 years old and self-learned (autodidact) Developer.
All i do professional now is self learned, as i have a passion for technology.
My mail Tech-Stack for the last 15 years was Web, PHP, MySQL, JS but i also worked with linux (bash/python), Oracle, MS SQL, SAP and many other technologies.

Since 2018 i work as CTO and Marketing Manager in a small Hotel/Hostel Chain in Vienna (3 Properties).
I am responsible for digitalisation, automation, marketing.
I've developed my own RMS for price generation with connections to Casablanca (PMS), Hotel-Spider (CM), Apaleo (PMS) wich serves also as BI-Platform and datawarehouse.

Till this hackathon the LeiwandLock connection was integrated in this RMS/BI-Platform wich is connected via custom App integration to apaleo.
The hackathon was a good chance to seperate LeiwandLock and make it a generic Appstore-App.
I am also co-founder of a business consulting company specialized in hospitality, where i sell the RMS, LeiwandLock and my expertise. As i am always tending to recommend the technology i've choosen for my hotels, apaleo is the best core for a highly automated, smart, customized and inovative solution for every modern hotel.

The Team behind LeiwandLock is ... me.
I invented LeiwandLock, made a prototype for proofe of concept, let a electronics labor develop professional electronics, dfeveloped the Lock-Software, Server-Software, Apaleo Connector.
And it is in use in production in a Hotel in Vienna (36 Locks).

![LeiwandLock](https://app.leiwand-lock.at/Logo_LEIWAND_III-b6-stretch.png)
[visit leiwand-lock.at](https://leiwand-lock.at)

## License

MIT
