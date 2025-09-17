const hre = require("hardhat");
const ethers = hre.ethers;

async function main() {
  const [owner, addr1, addr2] = await ethers.getSigners();

  // ➤ Reemplaza con tu dirección desplegada en localhost
  const contractAddress = "0xe7f1725E7734CE288F8367e1Bb143E90bb3F0512";

  const Nextia = await ethers.getContractFactory("NextiaToken");
  const token = await Nextia.attach(contractAddress);

  console.log("Contrato:", contractAddress);

  // Balance inicial del owner
  let bOwner = await token.balanceOf(owner.address);
  console.log("Owner balance (raw):", bOwner.toString());

  // Transferir 100 tokens (usar unidades con 18 decimales)
  const amount100 = ethers.parseUnits("100", 18);
  let tx = await token.transfer(addr1.address, amount100);
  await tx.wait();
  console.log("Transferidos 100 tokens a:", addr1.address);

  // Balance addr1
  let b1 = await token.balanceOf(addr1.address);
  console.log("Addr1 balance (raw):", b1.toString());

  // Mint 500 tokens (solo owner)
  tx = await token.connect(owner).mint(owner.address, ethers.parseUnits("500", 18));
  await tx.wait();
  console.log("Mint 500 al owner");

  // Burn 50 desde addr1
  tx = await token.connect(addr1).burn(ethers.parseUnits("50", 18));
  await tx.wait();
  console.log("Addr1 quemó 50");

  // Pausar (owner)
  await token.connect(owner).pause();
  console.log("Token pausado (owner)");

  // Intentar transferir (debe fallar)
  try {
    tx = await token.transfer(addr2.address, ethers.parseUnits("1", 18));
    await tx.wait();
    console.log("ERROR: Transfer tuvo éxito mientras pausado (no debería)");
  } catch (e) {
    console.log("Transfer bloqueado mientras pausado (esperado)");
  }

  // Unpause y transfer
  await token.connect(owner).unpause();
  console.log("Token despausado (owner)");

  tx = await token.transfer(addr2.address, ethers.parseUnits("1", 18));
  await tx.wait();
  console.log("Transfer luego de unpause OK - addr2 balance:", (await token.balanceOf(addr2.address)).toString());
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});


