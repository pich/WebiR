one_variable=function(var) {
	if (any(class(var)=='factor')) {
		N <- table(factor(var)) #stworzenie tabeli z częstotliwościami
		pcent <- 100*N/sum(N) # to samo procentowo
		write.csv2(cbind(N, pcent), file='result.csv')
	} else {
		result=as.matrix(summary(var))
		result[7]=length(var)-result[7]
		write.csv2(result, file='result.csv')
	}
}
