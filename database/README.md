Database VM components for IT490 Project

This folder contains everything for the Database Virtual Machine. 

The database runs on its own Vm and does NOT allow direct connections from the web server.
All communication between the web server and database server happens through RabbitMQ.

Contents

sql
Database schema for users and sessions tables.

listener
PHP RabbitMQ listener that:
 - Handles registration
 - Handles login
 - Generates session keys
 - Validates sessions

docs
Set instructions for configuring the Database VM.

Flow

Web VM -> RabbitMQ -> Database Listener -> MySQL
Response goes back through RabbitMQ.
