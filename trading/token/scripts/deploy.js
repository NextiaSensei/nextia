const hre = require("hardhat");

async function main() {
  const [owner] = await hre.ethers.getSigners();

  const NextiaToken = await hre.ethers.getContractFactory("NextiaToken");
  const token = await NextiaToken.deploy(
    hre.ethers.parseUnits("1000000", 18), // supply inicial = 1 millón
    owner.address                         // dueño inicial
  );

  await token.waitForDeployment();
  console.log(`Nextia Token deployed to: ${token.target}`);
}

main().catch((error) => {
  console.error(error);
  process.exitCode = 1;
});

