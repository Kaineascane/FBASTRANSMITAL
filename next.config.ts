import type { NextConfig } from 'next';

const nextConfig: NextConfig = {
  reactStrictMode: true,
  serverExternalPackages: ['better-sqlite3'],
  experimental: {
    serverActions: {
      allowedOrigins: [
        'localhost:3000',
        '127.0.0.1:3000',
        '*.devtunnels.ms',
        '*.asse.devtunnels.ms',
      ],
    },
  },
};

export default nextConfig;
