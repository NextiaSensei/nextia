const hre = require("hardhat");
const fs = require("fs");
const path = require("path");

async function main() {
  console.log("🚀 Iniciando despliegue en red:", hre.network.name);

  // 📌 Cuenta que va a desplegar
  const [deployer] = await hre.ethers.getSigners();
  const balance = await hre.ethers.provider.getBalance(deployer.address);
  console.log("👤 Deployer:", deployer.address);
  console.log("💰 Balance (ETH):", hre.ethers.formatEther(balance));

  // ⚙️ Preparar contrato
  const NextiaToken = await hre.ethers.getContractFactory("NextiaToken");

  // ✅ Inicial supply de 1 millón (con 18 decimales)
  const initialSupply = hre.ethers.parseUnits("1000000", 18);

  console.log("⏳ Desplegando contrato NextiaToken...");
  const token = await NextiaToken.deploy(initialSupply, deployer.address);

  // Esperar confirmación
  await token.waitForDeployment();

  const contractAddress = await token.getAddress();
  console.log("✅ NextiaToken desplegado en:", contractAddress);

  // 📂 Guardar datos de despliegue
  const deploymentsDir = path.join(__dirname, "..", "deployments");
  if (!fs.existsSync(deploymentsDir)) {
    fs.mkdirSync(deploymentsDir);
  }

  const deploymentData = {
    name: "NextiaToken",
    address: contractAddress,
    network: hre.network.name,
    deployer: deployer.address,
    timestamp: new Date().toISOString(),
    abi: JSON.parse(token.interface.formatJson()), // Guardamos ABI
  };

  const filePath = path.join(deploymentsDir, "NextiaToken.json");
  fs.writeFileSync(filePath, JSON.stringify(deploymentData, null, 2));

  console.log("📂 Guardado en:", filePath);
  console.log("🎉 Deploy finalizado correctamente.");
}

main().catch((err) => {
  console.error("❌ Error en el despliegue:", err);
  process.exitCode = 1;
});

