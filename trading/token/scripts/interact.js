const hre = require("hardhat");
const ethers = hre.ethers;
require("dotenv").config();

async function getContract() {
  const Nextia = await ethers.getContractFactory("NextiaToken");
  return Nextia.attach(process.env.NEXTIA_CONTRACT);
}

async function main() {
  const [owner, addr1, addr2] = await ethers.getSigners();
  const token = await getContract();

  const args = process.argv.slice(2);
  const command = args[0];

  switch (command) {
    case "balanceOf":
      const who = args[1] === "owner" ? owner.address : (args[1] === "addr1" ? addr1.address : addr2.address);
      console.log("Balance:", (await token.balanceOf(who)).toString());
      break;

    case "transfer":
      const to = args[1] === "addr1" ? addr1.address : addr2.address;
      const amount = ethers.parseUnits(args[2], 18);
      const tx1 = await token.transfer(to, amount);
      await tx1.wait();
      console.log(`Transferidos ${args[2]} tokens a ${args[1]}`);
      break;

    case "mint":
      const mintAmount = ethers.parseUnits(args[2], 18);
      const tx2 = await token.mint(owner.address, mintAmount);
      await tx2.wait();
      console.log(`Mint ${args[2]} al owner`);
      break;

    case "burn":
      const burnAmount = ethers.parseUnits(args[2], 18);
      const tx3 = await token.connect(addr1).burn(burnAmount);
      await tx3.wait();
      console.log(`Addr1 quemó ${args[2]}`);
      break;

    case "pause":
      await token.pause();
      console.log("Token pausado");
      break;

    case "unpause":
      await token.unpause();
      console.log("Token despausado");
      break;

    case "test-suite":
      console.log("Ejecutando pruebas automáticas...");
      // aquí podemos correr tu flujo original entero como test
      break;

    default:
      console.log("Comando no reconocido");
  }
}

main().catch((err) => {
  console.error(err);
  process.exitCode = 1;
});

