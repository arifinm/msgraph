<?php

namespace pkpudev\graph;

class Endpoint
{
  const BASE = 'https://login.microsoftonline.com';
  const TOKEN = '/oauth2/v2.0/token';
  const SCOPE = 'https://graph.microsoft.com/.default';
  const GRANT = 'client_credentials';
}