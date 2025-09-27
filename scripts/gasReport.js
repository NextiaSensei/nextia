const hre = require("hardhat");
const fs = require("fs");

async function main() {
  // ✅ Traemos ethers desde Hardhat Runtime Environment
  const { ethers } = hre;

  // 🔹 Obtenemos las cuentas del nodo Hardhat
  const [owner, addr1] = await ethers.getSigners();

  // 🔹 Instanciamos nuestro contrato
  const NextiaToken = await ethers.getContractFactory("NextiaToken");

  // 🔹 Cantidad inicial: 1,000,000 NXT con 18 decimales
  const initialSupply = ethers.utils.parseUnits("1000000", 18);

  // 🔹 Deploy
  const token = await NextiaToken.deploy(initialSupply, owner.address);
  await token.deployed();
  console.log("NextiaToken deployed at:", token.address);

  // 🔹 Array donde guardamos resultados de gas
  const report = [];

  // 🔹 Función helper para medir gas de una transacción
  async function measureGas(txFunc, description) {
    const tx = await txFunc();
    const receipt = await tx.wait();
    report.push(`${description}: ${receipt.gasUsed.toString()} gas`);
  }

  // 🔹 Pruebas de gas
  await measureGas(() => token.transfer(addr1.address, ethers.utils.parseUnits("1000", 18)), "Transfer");
  await measureGas(() => token.mint(owner.address, ethers.utils.parseUnits("1000", 18)), "Mint");
  await measureGas(() => token.burn(ethers.utils.parseUnits("500", 18)), "Burn");
  await measureGas(() => token.pause(), "Pause");
  await measureGas(() => token.unpause(), "Unpause");

  // 🔹 Guardar reporte en archivo
  const outputFile = "scripts/gas-report-script.txt";
  fs.writeFileSync(outputFile, report.join("\n"));
  console.log(`✅ Gas report generado: ${outputFile}`);
}

main()
  .then(() => process.exit(0))
  .catch((error) => {
    console.error(error);
    process.exit(1);
  });





