# 📊 Tokenomics de NextiaToken (Borrador)

**Nombre:** NextiaToken (NXT)  
**Decimals:** 18

## 1. Supply
- **Initial supply:** 1,000,000 NXT
- **Max supply:** (definir si aplica) — si no quieres límite, dejar como dinámico con control de mint.

## 2. Minting
- ¿Permitido? → **Sí / No** (definir)
- Si **Sí**: mint controlado por **multisig** (recomendado) o por `owner` con límites.
- Límite por emisión / anual: (ej.: max 5% total por año)

## 3. Distribución inicial (ejemplo)
- 40% Liquidez (DEX/CEX)
- 25% Equipo (vesting 24 meses, 6 meses cliff)
- 20% Comunidad (airdrops, incentivos)
- 10% Reservas
- 5% Marketing

## 4. Burn
- ¿Habilitado? Sí (función `burn()` pública permitida)
- Política de quema: (ej.: % de fees quemados o eventos programados)

## 5. Vesting / Timelock
- Equipo: vesting lineal 24 meses con cliff 3 meses.
- Reservas: desbloqueos trimestrales.
- Implementar **Timelock** para cambios administrativos.

## 6. Gobernanza / Owner
- Owner actual: EOA (deployer)
- **Plan antes de mainnet:** migrar a **Gnosis Safe (multisig)** + timelock.

## 7. Riesgos y mitigaciones
- Owner con mint ilimitado → riesgo de inflación. Mitigar con multisig y límites.
- Auditoría antes de mainnet.
- Tests adicionales para edge-cases y ataques.

## 8. Notas operativas
- Backup deployments/ + artifacts + ABI después de cada deploy.
- Mantener `.env` fuera de Git (usar `.env.example`).
