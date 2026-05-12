ARG NODE_VERSION=20.18.1-alpine

# ---------------------------------------------------------------
# Base image — pnpm via corepack
# ---------------------------------------------------------------
FROM node:${NODE_VERSION} AS base

ENV PNPM_HOME=/pnpm
ENV PATH=$PNPM_HOME:$PATH
# Node's bundled corepack ships with package-manager signing keys that go stale
# fast; upgrading first lets `corepack prepare pnpm@…` verify newer releases
# without `COREPACK_INTEGRITY_KEYS=0`.
RUN npm install -g corepack@latest \
    && corepack enable \
    && corepack prepare pnpm@10.33.2 --activate
WORKDIR /app

# ---------------------------------------------------------------
# Dependencies — install once and cache the result
# ---------------------------------------------------------------
FROM base AS deps

COPY package.json pnpm-lock.yaml ./
RUN --mount=type=cache,id=pnpm,target=/pnpm/store \
    pnpm install --frozen-lockfile

# ---------------------------------------------------------------
# Development — used by docker-compose for `pnpm dev`
# ---------------------------------------------------------------
FROM base AS development

ENV NODE_ENV=development
COPY --from=deps /app/node_modules ./node_modules
COPY . .

EXPOSE 3000
CMD ["pnpm", "dev"]

# ---------------------------------------------------------------
# Build — produces the standalone output
# ---------------------------------------------------------------
FROM base AS build

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1

COPY --from=deps /app/node_modules ./node_modules
COPY . .
RUN pnpm build

# ---------------------------------------------------------------
# Runtime — minimal production image
# ---------------------------------------------------------------
FROM node:${NODE_VERSION} AS runtime

ENV NODE_ENV=production
ENV NEXT_TELEMETRY_DISABLED=1
ENV PORT=3000

WORKDIR /app

RUN addgroup --system --gid 1001 nodejs \
    && adduser --system --uid 1001 nextjs

COPY --from=build --chown=nextjs:nodejs /app/.next/standalone ./
COPY --from=build --chown=nextjs:nodejs /app/.next/static ./.next/static
COPY --from=build --chown=nextjs:nodejs /app/public ./public

USER nextjs
EXPOSE 3000
CMD ["node", "server.js"]
