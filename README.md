# 🎥 YouTube Video Search Bot via GPT & Embeddings

A Telegram bot that helps find specific moments in your YouTube videos. It uses video transcripts, embeddings, and GPT to perform semantic search and returns results via a Telegram interface.

## 📌 Features

- 🔍 Semantic search through YouTube captions.
- 📥 Fetches video list from a playlist.
- 🧾 Parses captions from SRT YouTube API.
- 🧠 Generates embeddings (via OpenAI or Cohere).
- 📤 Indexes embeddings into Pinecone.
- 💬 Search interface via Telegram bot.
- 🧩 Clean layered architecture with clearly separated responsibilities: commands, controllers, services, and repositories.

## 🛠️ Tech Stack

- [Slim Framework](https://www.slimframework.com/)
- [Symfony Console](https://symfony.com/doc/current/components/console.html)
- [OpenAI / Cohere API](https://platform.openai.com/docs/guides/embeddings)
- [Pinecone](https://www.pinecone.io/)
- [Telegram Bot API](https://core.telegram.org/bots/api)

## 🚀 Installation & Setup

### 1. Clone the repository and install dependencies

```bash
git clone https://github.com/your-username/youtube-gpt-search-bot.git
cd youtube-gpt-search-bot
composer install
```

### 2. Create the configuration file
```bash
cp config/settings.php.dist config/settings.php
```

Then edit config/settings.php and fill in your API credentials (OpenAI, Pinecone, Telegram, YouTube, etc.).

### 3. Run CLI commands
Fetch video list from a playlist
```bash
php console/app.php fetch-list
```
Download and parse captions
```bash
php console/app.php fetch-captions
```
Generate embeddings and index them
```bash
php console/app.php index-captions
```
### 4. Start the Telegram bot
https://t.me/BurmAlexYoutubeBot

## 🤝 Contributions
Feel free to submit issues or pull requests if you'd like to improve this project.
