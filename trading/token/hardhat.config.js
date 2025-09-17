require("@nomicfoundation/hardhat-toolbox");

/** @type import('hardhat/config').HardhatUserConfig */
module.exports = {
  defaultNetwork: "localhost",
  solidity: "0.8.20",
  networks: {
    localhost: {
      url: "http://127.0.0.1:8545"
    },
    // ejemplo para testnet Sepolia (descomenta y usa .env con KEY y RPC)
    // sepolia: {
    //   url: `https://eth-sepolia.g.alchemy.com/v2/${process.env.ALCHEMY_API_KEY}`,
    //   accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : []
    // },
    // ejemplo para Polygon Mumbai
    // mumbai: {
    //   url: `https://polygon-mumbai.g.alchemy.com/v2/${process.env.ALCHEMY_API_KEY}`,
    //   accounts: process.env.PRIVATE_KEY ? [process.env.PRIVATE_KEY] : []
    // }
  }
};

