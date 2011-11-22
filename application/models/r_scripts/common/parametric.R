parametrycznosc=function(zx,writeResult=FALSE) {
 #Normalność rozkładu
 library(nortest)
 zx <- subset(zx, complete.cases(zx)==TRUE)
 lillie <- lillie.test(zx)  #Lilliefors (Kolmogorov-Smirnov) test for normality
 pearson <- pearson.test(zx)  #Pearson chi-square test for normality
 if(length(zx)>5000) {
  szx <- sample(subset(zx, complete.cases(zx)==TRUE), size=5000)
  shapiro <- shapiro.test(szx)  #Test Shapiro-Wilka
  sf <- sf.test(szx)     #Test Shapiro-Francia
 } else {
  shapiro <- shapiro.test(zx)  #Test Shapiro-Wilka
  sf <- sf.test(zx)     #Test Shapiro-Francia  
 }
 if (length(zx)>1000) {
  szx <- sample(subset(zx, complete.cases(zx)==TRUE), size=1000)
  ad <- ad.test(szx)    #Anderson-Darling test for normality - radzi sobie tylko z niewielkimi prókami
  cvm <- cvm.test(szx)    #Cramer-von Mises test for normality
 } else {
  ad <- ad.test(zx)    #Anderson-Darling test for normality - radzi sobie tylko z niewielkimi prókami
  cvm <- cvm.test(zx)    #Cramer-von Mises test for normality
 }
# Jeżeli przynajmniej jeden z testów daje $p.value >0.05, ale nie NA lub Inf, to przyjmujemy, że założenie spełnione
 wynik <- c(lillie$statistic, pearson$statistic, shapiro$statistic, sf$statistic, ad$statistic, cvm$statistic) 
 istotnosc <- c(lillie$p.value, pearson$p.value, shapiro$p.value, sf$p.value, ad$p.value, cvm$p.value)
 cbind(wynik, istotnosc) -> wyniki
 rownames(wyniki) <- c('Lilliefors (Kolmogorov-Smirnov)', 'Pearson chi-square',
   'Shapiro-Wilk', 'Shapiro-Francia', 'Anderson-Darling','Cramer-von Mises')
 wyniki[,2][wyniki[,2] >= 1] <- 0
 wyniki[,2][is.nan(wyniki[,2])] <- 0
 if (any(wyniki[,2]>=0.05)) {is_param=TRUE} else {is_param=FALSE}
 if(writeResult == TRUE) {
 	write.csv2(is_param,file="parametric.csv")
 	write.csv2(wyniki,file="statistics.csv")
 }
 parametrycznosc = is_param
}