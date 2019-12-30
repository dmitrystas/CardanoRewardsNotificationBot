## Description

CardanoRewardsNotificationBot can notify you about staking rewards to your telegram

## Usage

Start this bot [https://t.me/CardanoRewardsNotificationBot](https://t.me/CardanoRewardsNotificationBot) and subscribe to rewards notifications

## Commands

* `/getrewards <ADDR>` show rewards address info
* `/subscriberewards <ADDR>` subscribe to rewards notifications
* `/unsubscriberewards <ADDR>` unsubscribe from rewards notifications
* `/help` show a help about the rewards address

## Self-Install

* Create a new telegram bot
* Clone repo to your web-root folder and configure it (/CardanoRewardsNotificationBot/include/Config.php)
* Set webhook url https://yoursite/CardanoRewardsNotificationBot/webhook.php
* Сonfigure cron to run https://yoursite/CardanoRewardsNotificationBot/notify.php as often as necessary

## Dependences

CardanoRewardsNotificationBot requires [Jormungandr](https://github.com/input-output-hk/jormungandr), you need to install it (to cgi-bin folder by default) before using this bot. Additionally, if you don't know your rewards address and want to get it from your mnemonic phrase, it requires [Cardano-Wallet](https://github.com/input-output-hk/cardano-wallet)

## Getting the rewards address by yourself

Get a private key from the mnemonic

```
echo -e "<15 WORDS MNEMONIC>\n" | ./cardano-wallet mnemonic reward-credentials
```

Get the rewards address from the private key

```
./jcli address account --testing --prefix addr $(echo <PRIVATE_KEY> | ./jcli key to-public)
```
