#system("svn log > fs2_open.svnlog")
x <- readLines("fs2_open.svnlog")
rx <- x[grep("^r[0-9]{1,5} \\|",x)]
who <- gsub(" ","",sapply(strsplit(rx,"\\|"),"[",2))
ctab <- table(who)
pie(ctab[order(ctab)])
#,
#  scales=list(x=list()),
#  xlab="",
#  main="Commity")
