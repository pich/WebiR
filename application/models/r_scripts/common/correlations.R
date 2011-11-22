korelacja.nieparametryczna = function(zx,zy,forced=FALSE) {
	korelacja <- cor.test(zx, zy, alternative = 'two.sided', method = 'spearman', conf.level = 0.95)	
	wyniki <- c(FALSE,forced,NULL, NULL, korelacja$method, korelacja$p.value, korelacja$estimate)
	names(wyniki) <- c('parametryczna','wymuszona',"metoda","istotnosc","wspolczynnik")
	write.csv2(wyniki, file='korelacja.csv')
}

korelacja.parametryczna = function(zx,zy,forced=FALSE) {
	library(pwr)
	korelacja <- cor.test(zx, zy, alternative = 'two.sided', method = 'pearson', conf.level = 0.95)
	moc <- pwr.r.test(n =  korelacja$parameter+2, r = korelacja$estimate, sig.level = 0.05,power = NULL, alternative = "two.sided")
	wyniki <- c(TRUE,forced, NULL, NULL, korelacja$method, korelacja$p.value, korelacja$estimate, moc$n, moc$sig.level, moc$power)
	names(wyniki) <- c('parametryczna','wymuszona','metoda', 'istotnosc', 'wspolczynnik', 'liczebnosc', 'zalozona istotnosc', 'moc korelacji')
	write.csv2(wyniki, file='korelacja.csv')
}

korelacja=function(zx, zy) {
	zx<-as.numeric(zx)
	zy<-as.numeric(zy)
	param_1 <-parametrycznosc(zx)
	param_2 <-parametrycznosc(zy)
	
	if(param_1==FALSE || param_2==FALSE) {
		korelacja.nieparametryczna(zx,zy)
	} else {
		korelacja.parametryczna(zx,zy)
	}
}
