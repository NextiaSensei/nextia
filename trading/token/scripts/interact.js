const hre = require("hardhat");

async function main() {
  // 1. Dirección del contrato (la que obtuviste al hacer deploy en localhost)
  const contractAddress = "0x5FbDB2315678afecb367f032d93F642f64180aa3"; 

  // 2. Conectar con el contrato compilado
  const Nextia = await hre.ethers.getContractFactory("NextiaToken");
  const nextia = await Nextia.attach(contractAddress);

  // 3. Obtener las cuentas locales (signers)
  const [owner, addr1] = await hre.ethers.getSigners();

  // 4. Ver balance inicial del owner
  let balanceOwner = await nextia.balanceOf(owner.address);
  console.log("Balance inicial del owner:", balanceOwner.toString());

  // 5. Transferir 100 tokens de owner → addr1
  const tx = await nextia.transfer(addr1.address, 100);
  await tx.wait();

  console.log(`Transferidos 100 tokens a: ${addr1.address}`);

  // 6. Ver nuevo balance de addr1
  let balanceAddr1 = await nextia.balanceOf(addr1.address);
  console.log("Balance de addr1:", balanceAddr1.toString());
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});
