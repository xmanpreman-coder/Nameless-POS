/**
 * Stub signer untuk electron-builder
 * Skip signing process sepenuhnya
 */
module.exports = async (options) => {
  console.log('⊘ Signing skipped (stub signer)');
  return;
};
