# test Wilcoxona (jeżeli factor na dwóch poziomach i brak rozkładu normalnego)
wilcoxon=function(zx, zy) {
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}	
	liczbaobserwacji <- sum(table(factor(response),factor(group)))
	liczgrupA <- sum(table(factor(response),factor(group))[,1])
	liczgrupB <- sum(table(factor(response),factor(group))[,2])
	chisq.test(c(liczgrupA,liczgrupB)) -> chikw
	if (chikw$p.value<0.01) rownoliczne=FALSE else rownoliczne =TRUE
	if (liczgrupA<liczgrupB) {
		stosunekl <- abs(liczgrupB/liczgrupA)} else {
		stosunekl <- abs(liczgrupA/liczgrupB)
	}	
	twilcox <- wilcox.test(response~group, correct = TRUE)
	wariant=2
	wyniki <- c(wariant, rownoliczne, stosunekl, twilcox$statistic, twilcox$p.value, liczgrupA, liczgrupB)
	names(wyniki) <- c('Wariant analizy', 'Równoliczność grup', 'Stosunek liczbności grup jak 1:', 'Wartość testu W', 'Istotność', 'Liczebność grupa 1', 'Liczebność grupa 2')
	write.csv2(wyniki, file='wilcoxon.csv')
}	