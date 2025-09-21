const hre = require("hardhat");
const fs = require("fs");
const path = require("path");

async function main() {
  const [deployer] = await hre.ethers.getSigners();

  console.log("Desplegando contrato con la cuenta:", deployer.address);

  const NextiaToken = await hre.ethers.getContractFactory("NextiaToken");

  // Inicial supply de 1 millón (con 18 decimales)
  const initialSupply = hre.ethers.parseUnits("1000000", 18);

  const token = await NextiaToken.deploy(initialSupply, deployer.address);
  await token.waitForDeployment();

  const contractAddress = await token.getAddress();
  console.log("Nextia Token deployed to:", contractAddress);

  // ✅ Guardar en deployments/NextiaToken.json
  const deploymentsDir = path.join(__dirname, "..", "deployments");
  if (!fs.existsSync(deploymentsDir)) {
    fs.mkdirSync(deploymentsDir);
  }

  const deploymentData = {
    address: contractAddress,
    network: hre.network.name,
    deployer: deployer.address,
    timestamp: new Date().toISOString(),
    abi: JSON.parse(token.interface.formatJson()), // Guardamos el ABI
  };

  fs.writeFileSync(
    path.join(deploymentsDir, "NextiaToken.json"),
    JSON.stringify(deploymentData, null, 2)
  );

  console.log("📂 Guardado en deployments/NextiaToken.json");
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});


