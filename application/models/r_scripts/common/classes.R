wypluj.klasy=function(x) {
	klasy=data.frame()
	for (i in 1:length(x)) {
		wsio <- (class(x[,i]))
		for (k in 1:length(wsio)) wsio[k] -> klasy[i,k]
	}
	row.names(klasy) <- names(x)
	write.csv2(klasy, file='classes.csv')
}