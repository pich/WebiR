# Kruskall-Wallis czyli nieparametryczna ANOVA
kraskal=function(zx, zy){
	if(any(class(zx) == 'factor')) {
		group <- zx
		response <- zy
	} else {
		response <- zx
		group <- zy
	}
	kraskal <- kruskal.test(response~group)
	suma <- sum(table(response,group)) # N do wyrzucenia na ekran
	wariant=4
	wyniki <- c(wariant, kraskal$statistic, kraskal$parameter, kraskal$p.value, suma)
	names(wyniki) <- c('Wariant analizy', 'Chi-kwadrat Kruskall-a', 'df', 'istotność', 'suma badanych')
	write.csv2(wyniki, file='kraskal.csv')
}
