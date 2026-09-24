<div align="center">
<img src="https://blockrender.dev/render/block/netherrack.png?size=128" width="96" alt="Netherrack"> <h1>Netherrack</h1>

A Lightweight, Fast, Easy-to-Use Minecraft PHP Proxy

</div>

⸻

# About

Netherrack is a lightweight Minecraft: Bedrock Edition proxy written in PHP.

It provides a networking layer between Bedrock clients and backend Minecraft servers, built with simplicity and extensibility in mind.

Netherrack is based on NetherNet and uses its networking foundation to provide a PHP-native Bedrock proxy.

⸻

# Current Status

Netherrack is currently in early development.

The project currently contains the core foundations required for communicating with Minecraft: Bedrock Edition clients, including:

* Bedrock networking
* RakNet-based communication
* UDP networking
* Client connection handling
* Bedrock login and authentication
* Packet handling
* Initial proxy communication

The proxy is functional as a development project, but its internal APIs and networking implementation are still subject to change.

⸻

# Architecture

```
Bedrock Client
      │
      │ NetherNet Protocol
      ▼
┌─────────────────┐
│   Netherrack    │
│      Proxy      │
└────────┬────────┘
         │
         │ RakNet / UDP
         ▼
   Backend Server

Netherrack sits between the Bedrock client and the backend server, handling the network communication between them.
```

⸻

# Project Structure

```
Netherrack/
├── src/
├── vendor/
├── Startup.php
├── TestAuth.php
├── config.json
├── composer.json
├── server_identity/
└── LICENSE
```
⸻

# Requirements

* PHP 8+
* Composer
* Minecraft: Bedrock Edition server

⸻

# Installation

- Clone the repository:

```git clone https://github.com/PixalsNetwork/Netherrack.git```
```cd Netherrack```

- Install dependencies:

```composer install```

- Configure:

```config.json```

- Start Netherrack:

```php Startup.php```

⸻

# Based on NetherNet

Netherrack is based on NetherNet, using it as the foundation for its Bedrock networking implementation.

The project is developed as an independent PHP proxy built around that foundation.

⸻

# License

Netherrack is released under the MIT License.

See LICENSE for the full license text.

