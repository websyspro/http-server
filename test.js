const generateUUID = () => {
  return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
    const r = Math.random() * 16 | 0;
    const v = c == 'x' ? r : (r & 0x3 | 0x8);
    return v.toString(16);
  });
};

const numberTest = 100;
const resultTest = {
  success: 0,
  fail: 0,
  totalTime: 0,
  minTime: Infinity,
  maxTime: 0
};

const httpRequestTest = async () => {
  try {
    const uuid = generateUUID();
    const startTime = performance.now();
    const response = await fetch(`http://localhost:3002/api/v1/base/test/timer?uuid=${uuid}`);
    const endTime = performance.now();
    const responseTime = endTime - startTime;
    const { success, content } = await response.json();

    if( success === true && content.uuid === uuid ){
      return { success: true, time: responseTime };      
    } else {
      return { success: false, time: responseTime };      
    }
  } catch (error) {
    return {
      success: false,
      time: 0,
      data: null
    };
  }
};

const runTests = async () => {
  const testStartTime = performance.now();
  
  // Criar todas as promises simultaneamente
  const promises = [];
  for (let i = 0; i < numberTest; i++) {
    promises.push(httpRequestTest());
  }
  
  console.log(`Executando ${numberTest} requests simultâneos...`);
  
  // Executar todas em paralelo
  const results = await Promise.all(promises);
  
  // Processar resultados
  results.forEach(result => {
    if (result.success) {
      resultTest.success++;
      resultTest.totalTime += result.time;
      resultTest.minTime = Math.min(resultTest.minTime, result.time);
      resultTest.maxTime = Math.max(resultTest.maxTime, result.time);
    } else {
      resultTest.fail++;
    }
  });
  
  const testEndTime = performance.now();
  const totalTestTime = (testEndTime - testStartTime) / 1000;
  const avgResponseTime = resultTest.success > 0 ? resultTest.totalTime / resultTest.success : 0;
  const requestsPerSecond = numberTest / totalTestTime;
  
  console.clear();
  console.log('=== RESUMO DOS TESTES (PARALELO) ===');
  console.log(`Total de requisições: ${numberTest}`);
  console.log(`Sucessos: ${resultTest.success}`);
  console.log(`Falhas: ${resultTest.fail}`);
  console.log(`Tempo total: ${totalTestTime.toFixed(2)}s`);
  console.log(`Requisições por segundo: ${requestsPerSecond.toFixed(2)} req/s`);
  console.log(`Tempo médio de resposta: ${avgResponseTime.toFixed(2)}ms`);
  console.log(`Tempo mínimo: ${resultTest.minTime.toFixed(2)}ms`);
  console.log(`Tempo máximo: ${resultTest.maxTime.toFixed(2)}ms`);
  console.log('====================================');
};

runTests();
