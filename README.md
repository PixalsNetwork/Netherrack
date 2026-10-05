# Netherrack

**A PHP-based networking and communication layer for Minecraft server networks.**

Netherrack is designed to provide a unified communication layer between a network proxy and its downstream Minecraft servers, allowing network-wide systems and services to be implemented without every server having to reinvent the same infrastructure.

> [!WARNING]
> **Netherrack must NOT be used with proxies or downstream servers that are directly exposed to the public Internet.**
>
> Netherrack assumes that the proxy and its downstream services operate within a **trusted, private environment**, such as the same VPS, private network, or otherwise isolated infrastructure.
>
> If communication between Netherrack and a downstream server crosses an untrusted network, then please wait for further updates that enables Upstream to Downstream Encryption System

---

## Overview

A typical Netherrack deployment looks like this:

```text
                         INTERNET
                            │
                            │
                      Secure connection
                            │
                            ▼
                    ┌───────────────┐
                    │  Netherrack   │
                    │     Proxy     │
                    └───────┬───────┘
                            │
                    Authenticated IPC
                            │
             ┌──────────────┼──────────────┐
             ▼              ▼              ▼
          Lobby          BedWars        Factions
```

Netherrack separates the **Internet-facing connection** from the **internal network communication layer**.

The player-facing connection is responsible for protecting traffic crossing the public Internet.

The internal communication layer assumes that the infrastructure hosting Netherrack and its downstream servers is trusted.

---

## Security Model

Netherrack intentionally does **not** treat every internal connection as an Internet connection.

### Client → Netherrack

This is an untrusted network boundary.

Client-facing communication should therefore use the appropriate encryption and authentication mechanisms.

```text
Player
   │
   │ Encrypted / authenticated
   ▼
Netherrack
```

### Netherrack → Downstream

When the downstream server is running on the same trusted machine or private infrastructure, Netherrack does not require application-level encryption for the internal transport.

```text
Netherrack
   │
   │ Authenticated internal communication
   ▼
Downstream
```

The downstream endpoint should be restricted so that it cannot be accessed directly from the public Internet.

For same-machine deployments, prefer:

- `127.0.0.1`
- Unix domain sockets
- Private network interfaces
- Firewall restrictions
- Authentication between Netherrack and downstream services

---

## Why Isn't Internal Traffic Encrypted?

Encryption is not automatically equivalent to security.

If an attacker has sufficient privileges to inspect traffic between two processes on the same machine, they may already have the ability to access the processes, memory, credentials, or host itself.

Netherrack therefore focuses its encryption requirements on the **actual untrusted boundary** rather than adding unnecessary cryptographic complexity to local communication.

This also keeps the communication layer simpler and easier to maintain.

If Netherrack and a downstream server are deployed on different machines across an untrusted network, the transport **must be protected with an appropriate encrypted channel**.

---

## Requirements

Netherrack is intended for controlled server-network infrastructure.

A recommended deployment should provide:

- A private or isolated network environment
- Restricted downstream ports
- Authentication between Netherrack and downstream services
- Proper firewall configuration
- Secure management of secrets
- An encrypted client-facing connection

### Never expose downstream services directly

Do **not** configure your infrastructure like this:

```text
Internet
   │
   ├──────────────► Netherrack
   │
   └──────────────► Downstream Server
```

Instead:

```text
Internet
   │
   ▼
Netherrack
   │
   ▼
Private Downstream Server
```

The downstream server should not be independently reachable by arbitrary Internet clients.

---

## What Netherrack Provides

Netherrack is intended to centralize common network functionality such as:

- Player identity
- Player transfers
- Network-wide ranks
- Permissions
- Clans
- Friends
- Network statistics
- Cross-server communication
- Server discovery
- Network events
- Shared player data
- Administrative communication
- Network-wide services

Instead of implementing these systems independently on every server, Netherrack provides a common communication layer.

```text
             Netherrack
                  │
       ┌──────────┼──────────┐
       │          │          │
       ▼          ▼          ▼
    Lobby      BedWars    Factions
       │          │          │
       └──────────┼──────────┘
                  │
           Shared Services
```

---

## Design Philosophy

Netherrack follows a few simple principles.

### 1. Keep the network unified

Network-wide functionality should have a single source of truth.

### 2. Don't duplicate infrastructure

Individual servers should not need to independently implement systems that belong to the network.

### 3. Keep communication simple

The communication layer should be predictable, debuggable, and easy to extend.

### 4. Secure the actual boundaries

Security should be based on the deployment's real threat model rather than adding cryptography everywhere by default.

### 5. Don't reinvent unnecessary infrastructure

Netherrack should provide the functionality needed by the network while relying on established technologies and infrastructure where appropriate.

---

## Architecture

Netherrack is designed around three major layers:

```text
┌─────────────────────────────────────────┐
│              Client Layer               │
│                                         │
│     Players / Minecraft Clients        │
└───────────────────┬─────────────────────┘
                    │
             Secure Transport
                    │
┌───────────────────▼─────────────────────┐
│            Netherrack Proxy             │
│                                         │
│  Routing • Authentication • Network API │
└───────────────────┬─────────────────────┘
                    │
          Internal Communication
                    │
┌───────────────────▼─────────────────────┐
│          Downstream Servers             │
│                                         │
│ Lobby • Games • Factions • etc.         │
└─────────────────────────────────────────┘
```

Netherrack acts as the network-level coordination layer rather than attempting to replace every component of the Minecraft server stack.

---

## Deployment

A recommended single-host deployment:

```text
                 VPS
┌────────────────────────────────────────────┐
│                                            │
│  Internet                                  │
│     │                                      │
│     ▼                                      │
│  Netherrack                                │
│     │                                      │
│     │ localhost / Unix socket              │
│     ▼                                      │
│  ┌─────────┬─────────┬─────────┐           │
│  │ Lobby   │ BedWars │ Factions│           │
│  └─────────┴─────────┴─────────┘           │
│                                            │
└────────────────────────────────────────────┘
```

Only Netherrack should need to be exposed to the public Internet.

---

## Remote Downstream Servers

Running downstream servers on separate machines is possible, but the security requirements change.

```text
VPS A                         VPS B

Netherrack  ═══ encrypted ═══►  Backend
```

Do not assume that the internal Netherrack protocol is safe simply because it is normally used on localhost.

When traffic crosses an untrusted network, protect the transport accordingly.

Possible approaches include:

- VPN/private networking
- TLS
- Encrypted tunnels
- Other established secure transport mechanisms

Do not expose the internal communication endpoint directly to the Internet without appropriate protection.

---

## Authentication

Netherrack should authenticate downstream services before allowing them to participate in the network.

Authentication credentials must:

- Never be committed to source control
- Never be distributed publicly
- Be stored securely
- Be rotated when compromised
- Be unique to the deployment where practical

Authentication establishes **who is allowed to communicate**.

Encryption establishes **who can read the communication while it crosses an untrusted network**.

These are separate security concerns.

---

## Threat Model

Netherrack is designed primarily to protect against threats crossing the public network boundary.

It does **not** attempt to protect against a fully compromised host.

If an attacker has root-level access to the machine running Netherrack and its downstream servers, the host itself should be considered compromised.

Netherrack is not intended to provide security against:

- A malicious root user
- A fully compromised VPS
- Malicious administrators with host-level access
- Malware already controlling the host
- Direct public exposure of downstream services

Host security remains the responsibility of the deployment.

---

## Development

Netherrack is primarily written in PHP.

The project is intended to provide a practical foundation for Minecraft network infrastructure without requiring every network to build its own communication architecture from scratch.

Contributions should favor:

- Simplicity
- Clear interfaces
- Minimal coupling
- Predictable behavior
- Testability
- Security based on realistic threat models

Avoid introducing cryptographic or networking complexity without a concrete requirement for it.

---

## License

See `LICENSE` for licensing information.

---

## Disclaimer

Netherrack is infrastructure software. **A secure deployment depends on how it is configured.**

Running Netherrack with publicly exposed downstream services, improperly configured firewalls, leaked credentials, or untrusted infrastructure can invalidate the security assumptions described above.

**Do not expose Netherrack's internal communication endpoints directly to the public Internet.**
